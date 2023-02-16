<?php

namespace ContentEgg\application\modules\CjProducts;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\cj\CjProductsRest;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\helpers\TextHelper;

/**
 * CjProductsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CjProductsModule extends AffiliateParserModule {

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'CJ Products',
            'api_agreement' => 'http://www.cj.com/legal/ws-terms',
            'description' => __('Adds products from CJ.com. You must have approval from each program separately.', 'content-egg'),
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/cjproducts',
            
        );
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'list';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();

        if ($is_autoupdate)
            $options['records-per-page'] = $this->config('entries_per_page_update');
        else
            $options['records-per-page'] = $this->config('entries_per_page');

        $fields = array(
            'website_id',
            'advertiser_ids',
            'low_price',
            'high_price',
            'low_sale_price',
            'high_sale_price',
            'serviceable_area',
            'currency',
            'sort_by',
            'sort_order',
            'manufacturer_name',
            'advertiser_sku',
        );

        foreach ($fields as $f)
        {
            if ($this->config($f))
                $options[str_replace('_', '-', $f)] = $this->config($f);
        }

        if (!empty($query_params['low_price']))
            $options['low-price'] = $query_params['low_price'];
        if (!empty($query_params['high_price']))
            $options['high-price'] = $query_params['high_price'];

        $results = $this->getCJClient()->search($keyword, $options);

        if (!is_array($results) || !isset($results['products']['product']))
            return array();

        $results = $results['products']['product'];

        if (!isset($results[0]) && isset($results['ad-id']))
            $results = array($results);

        return $this->prepareResults($results);
    }

    public function doRequestItems(array $items)
    {
        foreach ($items as $key => $item)
        {
            $options = array();
            $options['website-id'] = $this->config('website_id');

            if (!empty($item['extra']['advertiserId']))
                $options['advertiser-ids'] = $item['extra']['advertiserId'];
            if (!empty($item['isbn']))
                $options['isbn'] = $item['isbn'];
            if (!empty($item['upc']))
                $options['upc'] = $item['upc'];
            if (!empty($item['extra']['manufacturerSku']))
                $options['manufacturer-sku'] = $item['extra']['manufacturerSku'];

            $keyword = $item['title'];
            $keyword = str_replace('+', ' ', $keyword);
            $keyword = str_replace('-', ' ', $keyword);
            if (!isset($options['upc']) && !$item['isbn'] && !$options['manufacturer-sku'] && !isset($item['isbn']))
                $keyword = preg_replace('/\s+/', ' +', $keyword);
            //$options['keywords'] = $keyword;

            try
            {
                $results = $this->getCJClient()->search($keyword, $options);
            } catch (\Exception $e)
            {
                continue;
            }
            
            if (!isset($results['products']['product']))
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;                
                continue;
            }

            $results = $results['products']['product'];
            if (!isset($results[0]) && isset($results['ad-id']))
                $results = array($results);
            foreach ($results as $i => $r)
            {
                if ($item['extra']['catalogId'] != $r['catalog-id'])
                {
                    if ($i == count($results) - 1)
                        $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                    continue;
                }

                // assign new price   
                list($items[$key]['price'], $items[$key]['priceOld']) = self::getPrices($r);
                if (filter_var($r['in-stock'], FILTER_VALIDATE_BOOLEAN))
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
                else
                    $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                
                break;
            }
        }
        return $items;
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->title = $r['name'];
            $content->url = $r['buy-url'];
            $content->domain = TextHelper::parseDomain($content->url, 'url');

            $content->img = (!empty($r['image-url'])) ? $r['image-url'] : '';
            $content->manufacturer = ($r['manufacturer-name']) ? $r['manufacturer-name'] : '';
            $content->availability = filter_var($r['in-stock'], FILTER_VALIDATE_BOOLEAN);
            $content->currencyCode = $r['currency'];
            $content->currency = TextHelper::currencyTyping($content->currencyCode);

            $content->description = strip_tags($r['description']);
            if ($max_size = $this->config('description_size'))
                $content->description = TextHelper::truncate($content->description, $max_size);

            list($content->price, $content->priceOld) = self::getPrices($r);
            if (filter_var($r['in-stock'], FILTER_VALIDATE_BOOLEAN))
                $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;
            else
                $content->stock_status = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;

            $content->extra = new ExtraDataCjProducts;
            $content->extra->advertiserId = ($r['advertiser-id']) ? $r['advertiser-id'] : '';
            $content->extra->advertiserCategory = ($r['advertiser-category']) ? $r['advertiser-category'] : '';
            $content->extra->catalogId = ($r['catalog-id']) ? $r['catalog-id'] : '';
            $content->extra->isbn = ($r['isbn']) ? $r['isbn'] : '';
            $content->extra->manufacturerSku = ($r['manufacturer-sku']) ? $r['manufacturer-sku'] : '';
            $content->sku = $content->extra->sku = ($r['sku']) ? $r['sku'] : '';
            $content->upc = $content->extra->upc = ($r['upc']) ? $r['upc'] : '';

            // ad-id dublicate?            
            $content->unique_id = $r['ad-id'];
            if ($content->extra->isbn)
                $content->unique_id .= '-' . $content->extra->isbn;
            if ($content->extra->sku)
                $content->unique_id .= '-' . $content->extra->sku;
            if ($content->extra->upc)
                $content->unique_id .= '-' . $content->extra->upc;

            if (!$content->unique_id)
                $content->unique_id = md5($content->url);

            if (isset($data[$content->unique_id]))
            {
                $content->unique_id .= '-' . md5($content->title . $content->price);
            }

            $data[] = $content;
        }
        return $data;
    }

    private static function getPrices($r)
    {
        $return_price = 0;
        $return_price_old = 0;
        $sale_price = ($r['sale-price']) ? (float) $r['sale-price'] : 0;
        $retail_price = ($r['retail-price']) ? (float) $r['retail-price'] : 0;
        $price = (float) $r['price'];
        $price_array = array();
        if ($sale_price)
            $price_array[] = $sale_price;
        if ($retail_price)
            $price_array[] = $retail_price;
        if ($price)
            $price_array[] = $price;
        $price_array = array_unique($price_array);
        sort($price_array);
        // min price
        if ($price_array)
            $return_price = $price_array[0];
        // max price
        if (count($price_array) > 1)
            $return_price_old = $price_array[count($price_array) - 1];
        return array($return_price, $return_price_old);
    }

    private function getCJClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new CjProductsRest($this->config('access_token'), $this->config('dev_key'));
        }
        return $this->api_client;
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

}
