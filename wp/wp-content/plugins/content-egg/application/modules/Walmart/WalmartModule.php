<?php

namespace ContentEgg\application\modules\Walmart;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\walmart\WalmartApi;
use ContentEgg\application\modules\Walmart\ExtraDataWalmart;
use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\LinkHandler;

/**
 * WalmartModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2017 keywordrush.com
 */
class WalmartModule extends AffiliateParserModule {

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Walmart',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), 'Walmart.com'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/walmart',            
        );
    }

    public function releaseVersion()
    {
        return '4.1.0';
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'grid';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();

        if ($is_autoupdate)
            $limit = $this->config('entries_per_page_update');
        else
            $limit = $this->config('entries_per_page');

        $options['numItems'] = $limit;

        $params = array(
            'publisherId',
            'sort',
            'order',
            'responseGroup',
        );

        foreach ($params as $param)
        {
            $value = $this->config($param);
            if ($value)
                $options[$param] = $value;
        }

        if ($this->config('categoryId'))
            $options['categoryId'] = (int) $this->config('categoryId');

        //@link: https://developer.walmartlabs.com/docs/read/Item_Field_Description
        $options['responseGroup'] = 'full';

        // price filter
        if (!empty($query_params['price_min']))
            $price_min = (float) $query_params['price_min'];
        elseif ($this->config('price_min'))
            $price_min = (float) $this->config('price_min');
        else
            $price_min = 0;
        if (!empty($query_params['price_max']))
            $price_max = (float) $query_params['price_max'];
        elseif ($this->config('price_max'))
            $price_max = (float) $this->config('price_max');
        else
            $price_max = 0;
        if ($price_min && !$price_max)
            $price_max = 999999;
        if ($price_max && !$price_min)
            $price_min = 0;
        if ($price_min || $price_max)
        {
            $options['facet'] = 'on';
            $options['facet.range'] = 'price:[' . (int) $price_min . ' TO ' . (int) $price_max . ']';
        }

        $results = $this->getApiClient()->search($keyword, $options);

        if (!isset($results['items']) || !is_array($results['items']))
            return array();

        return $this->prepareResults($results['items']);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;

            $content->unique_id = $r['itemId'];
            $content->domain = 'walmart.com';
            $content->title = $r['name'];

            if (!empty($r['shortDescription']))
                $content->description = $r['shortDescription'];
            elseif (!empty($r['longDescription']))
                $content->description = $r['longDescription'];
            $content->description = strip_tags(html_entity_decode($content->description));
            if ($max_size = $this->config('description_size'))
                $content->description = TextHelper::truncateHtml($content->description, $max_size);

            if (!empty($r['upc']))
                $content->upc = $r['upc'];
            $content->categoryPath = explode('/', $r['categoryPath']);
            $content->category = current($content->categoryPath);
            if (!empty($r['brandName']))
                $content->manufacturer = $r['brandName'];
            $content->img = $r['largeImage'];
            if (!empty($r['customerRating']))
                $content->rating = TextHelper::ratingPrepare($r['customerRating']);
            if (!empty($r['numReviews']))
                $content->reviewsCount = (int) $r['numReviews'];
            if (!empty($r['sellerInfo']))
                $content->merchant = $r['sellerInfo'];

            // Possible values are [Available, Limited Supply, Last few items, Not available]
            $content->availability = $r['stock'];
            if ($r['stock'] == 'Not available')
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            else
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

            if (!empty($r['salePrice']))
                $content->price = $r['salePrice'];
            if (!empty($r['msrp']))
                $content->priceOld = $r['msrp'];

            $content->currencyCode = 'USD';
            $content->orig_url = TextHelper::parseOriginalUrl($r['productUrl'], 'l');
            $content->url = $this->generateAffiliateUrl($content->orig_url, $r);

            $content->extra = new ExtraDataWalmart();
            ExtraDataWalmart::fillAttributes($content->extra, $r);

            if ($this->config('customer_reviews'))
                $content->extra->comments = $this->parseComments($r['itemId']);

            $data[] = $content;
        }
        return $data;
    }

    protected function parseComments($item_id)
    {
        try
        {
            $results = $this->getApiClient()->reviews($item_id);
        } catch (\Exception $e)
        {
            return array();
        }
        if (!isset($results['reviews']) || !is_array($results['reviews']))
            return array();

        $reviews = array();
        foreach ($results['reviews'] as $r)
        {
            $review = array();

            $review['comment'] = strip_tags($r['reviewText']);
            if ($r['title'])
            {
                if (!preg_match('/[\.\!\?]$/', $r['title']))
                    $r['title'] = $r['title'] . '.';
                $review['comment'] = $r['title'] . ' ' . $review['comment'];
            }

            $review['name'] = sanitize_text_field($r['reviewer']);
            $review['rating'] = TextHelper::ratingPrepare($r['overallRating']['rating']);
            $review['date'] = strtotime($r['submissionTime']);
            $review['upVotes'] = (int) $r['upVotes'];
            $review['downVotes'] = (int) $r['downVotes'];

            $reviews[] = $review;
        }
        return $reviews;
    }

    public function doRequestItems(array $items)
    {
        // Lookup for mutiple item ids 12417832,19336123 (supports upto 20 items in one call):
        $pages_count = ceil(count($items) / 20);
        $results = array();

        $options = array();
        if ($this->config('publisherId'))
            $options['publisherId'] = $this->config('publisherId');

        for ($i = 0; $i < $pages_count; $i++)
        {
            $items20 = array_slice($items, $i * 20, 20);
            $item_ids = array_map(function($element) {
                return $element['unique_id'];
            }, $items20);
            $res = $this->getApiClient()->products($item_ids, $options);
            if (!isset($res['items']))
                continue;

            foreach ($res['items'] as $r)
            {
                $results[$r['itemId']] = $r;
            }
        }

        // assign new data
        foreach ($items as $key => $item)
        {
            if (!isset($results[$item['unique_id']]))
                continue;
            $r = $results[$item['unique_id']];
            if (!empty($r['customerRating']))
                $items[$key]['rating'] = TextHelper::ratingPrepare($r['customerRating']);
            if (!empty($r['numReviews']))
                $items[$key]['reviewsCount'] = (int) $r['numReviews'];
            $items[$key]['availability'] = $r['stock'];

            if ($r['stock'] == 'Not available')
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
            else
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
           
            $items[$key]['url'] = $this->generateAffiliateUrl($item['orig_url'], $r);
            
            if (!empty($r['salePrice']))
                $items[$key]['price'] = $r['salePrice'];
            else
                $items[$key]['price'] = null;
            if (!empty($r['msrp']))
                $items[$key]['priceOld'] = $r['msrp'];
        }
        return $items;
    }

    private function generateAffiliateUrl($url, array $r)
    {
        if ($deeplink = $this->config('deeplink'))
            return LinkHandler::createAffUrl($url, $deeplink);

        if (!empty($r['productTrackingUrl']))
            return $r['productTrackingUrl'];

        return 'https://goto.walmart.com/c/' . urlencode($this->config('publisherId')) . '/568844/9383?veh=aff&sourceid=imp_000011112222333344&u=' . urlencode($url);
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            if (!$apiKey = $this->config('apiKey'))
                $apiKey = 'nyv3vpvs3thzqtkwbzupnaze';
            $this->api_client = new WalmartApi($apiKey);
        }
        return $this->api_client;
    }

    public function presavePrepare($data, $post_id)
    {
        $data = parent::presavePrepare($data, $post_id);

        if ($post_id > 0 && $this->config('reviews_as_comments'))
        {
            // get reviews from module data
            $comments = ContentManager::getNormalizedReviews($data);
            if ($comments)
            {
                // save reviews as post comments
                ContentManager::saveReviewsAsComments($post_id, $comments);

                // remove reviews from module data
                $data = ContentManager::removeReviews($data);
            }
        }
        return $data;
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public function renderSearchPanel()
    {
        $this->render('search_panel', array('module_id' => $this->getId()));
    }

    public function renderUpdatePanel()
    {
        $this->render('update_panel', array('module_id' => $this->getId()));
    }

}
