<?php

namespace ContentEgg\application\modules\Kelkoo;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\libs\kelkoo\KelkooApi;
use ContentEgg\application\modules\Kelkoo\ExtraDataKelkoo;

/**
 * KelkooModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class KelkooModule extends AffiliateParserModule {

    private $api_client = null;
    private $merchants = array();

    public function info()
    {
        return array(
            'name' => 'Kelkoo',
            'description' => sprintf(__('Adds products from %s.', 'content-egg'), '<a target="_blank" href="https://www.kelkoogroup.com">Kelkoo Group</a> marketing platform'),
        );
    }

    public function releaseVersion()
    {
        return '4.5.0';
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

        $options['results'] = $limit;

        // price filter
        if (!empty($query_params['price_min']))
            $options['price_min'] = $query_params['price_min'];
        elseif ($this->config('price_min'))
            $options['price_min'] = $this->config('price_min');

        if (!empty($query_params['price_max']))
            $options['price_max'] = (int) $query_params['price_max'];
        elseif ($this->config('price_max'))
            $options['price_max'] = $this->config('price_max');

        $params = array(
            'sort',
            'logicalType',
            'automaticOr',
            'mobileFriendly',
            'boostMobileResults',
            'category',
            'merchantId',
        );

        foreach ($params as $param)
        {
            $value = $this->config($param);
            if ($value)
                $options[$param] = $value;
        }
        $options['show_products'] = 1;
        $options['imagesOverSsl'] = 1;
        $options['show_subcategories'] = 0;
        $options['show_refinements'] = 0;
        if ($this->config('rebatePercentage'))
            $options['rebatePercentage'] = (int) $this->config('rebatePercentage');

        if (TextHelper::isEan($keyword))
            $results = $this->getApiClient()->searchEan($keyword, $options); // EAN search
        else
            $results = $this->getApiClient()->search($keyword, $options);

        if (!isset($results['products']) || !isset($results['products']['product']))
            return array();

        return $this->prepareResults($results['products']['product']);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            if (!isset($r['offer']))
                continue;
            $r = $r['offer'];
            $content = new ContentProduct;

            $content->unique_id = $r['id'];
            $content->title = $r['title'];
            if (isset($r['description']))
                $content->description = strip_tags($r['description']);
            $content->url = $r['url'];
            if ($max_size = $this->config('description_size'))
                $content->description = TextHelper::truncateHtml($content->description, $max_size);
            if (isset($r['images']) && isset($r['images']['zoomImage']['url']))
                $content->img = $r['images']['zoomImage']['url'];
            if (!empty($r['merchant']))
                $content->merchant = $r['merchant']['name'];
            if (!empty($r['category']))
                $content->category = $r['category']['name'];
            $content->price = $r['price']['price'];
            if (!empty($r['price']['priceWithoutRebate']))
                $content->priceOld = $r['price']['priceWithoutRebate'];
            if (!empty($r['price']['currency']))
                $content->currencyCode = $r['price']['currency'];
            $content->availability = (int) $r['availability'];

            if ((int) $r['availability'])
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            else
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;

            if (!empty($r['brand']))
                $content->manufacturer = $r['brand'];
            if (!empty($r['ean']))
                $content->ean = $r['ean'];

            $content->extra = new ExtraDataKelkoo();
            ExtraDataKelkoo::fillAttributes($content->extra, $r);

            $this->fillMerchantInfo($content);
            $data[] = $content;
        }
        return $data;
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            $result = $this->getApiClient()->offer($item['unique_id']);
            if (!isset($result['products']) || !isset($result['products']['product']) || !isset($result['products']['product'][0]))
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                continue;
            }
            $r = $result['products']['product'][0]['offer'];

            // assign new data
            if ($r['url'])
                $items[$key]['url'] = $r['url'];
            if (!empty($r['price']['price']))
                $items[$key]['price'] = $r['price']['price'];
            else
                $items[$key]['price'] = '';
            if (!empty($r['price']['priceWithoutRebate']))
                $items[$key]['priceOld'] = $r['price']['priceWithoutRebate'];
            else
                $items[$key]['priceOld'] = '';
            $items[$key]['currencyCode'] = $r['price']['currency'];
            $items[$key]['availability'] = (int) $r['availability'];

            if ((int) $r['availability'])
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            else
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
        }
        return $items;
    }

    private function fillMerchantInfo($content)
    {
        if (!$content->extra->merchant['id'])
            return;
        $merchant_id = $content->extra->merchant['id'];

        if (!isset($this->merchants[$merchant_id]))
        {
            try
            {
                $result = $this->getApiClient()->merchant($merchant_id);
            } catch (\Exception $e)
            {
                return;
            }
            if (!isset($result['merchant']))
                return;
            $this->merchants[$merchant_id] = $result['merchant'][0];
        }

        if ($this->merchants[$merchant_id]['merchantUrl'])
            $content->domain = TextHelper::getHostName($this->merchants[$merchant_id]['merchantUrl']);
        if ($this->merchants[$merchant_id]['profile']['logo'])
            $content->logo = $this->merchants[$merchant_id]['profile']['logo']['url'];
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new KelkooApi($this->config('region'), $this->config('trackingId'), $this->config('affiliateKey'));
        }
        return $this->api_client;
    }

    public function renderSearchPanel()
    {
        $this->render('search_panel', array('module_id' => $this->getId()));
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

    public function renderUpdatePanel()
    {
        $this->render('update_panel', array('module_id' => $this->getId()));
    }

}
