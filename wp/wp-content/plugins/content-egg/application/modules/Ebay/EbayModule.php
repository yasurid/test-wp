<?php

namespace ContentEgg\application\modules\Ebay;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\ebay\EbayFinding;
use ContentEgg\application\libs\ebay\EbayShopping;
use ContentEgg\application\components\ContentProduct;
use ContentEgg\application\admin\PluginAdmin;
use ContentEgg\application\components\ExtraData;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\components\LinkHandler;

/**
 * EbayModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class EbayModule extends AffiliateParserModule {

    private $api_client_finding = null;
    private $api_client_shopping = null;

    public function info()
    {
        return array(
            'name' => 'Ebay',
            'docs_uri' => 'https://ce-docs.keywordrush.com/modules/affiliate/ebay',
        );
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_PRODUCT;
    }

    public function defaultTemplateName()
    {
        return 'data_list';
    }

    public function isItemsUpdateAvailable()
    {
        return true;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $options = array();
        $global_id = $this->config('global_id');

        // Affiliate section
        $options['affiliate'] = array();
        $options['affiliate']['customId'] = $this->config('custom_id');
        $options['affiliate']['networkId'] = 9; //9 = eBay Partner Network
        if ($this->config('tracking_id'))
            $options['affiliate']['trackingId'] = $this->config('tracking_id');

        // Pagination
        $options['paginationInput'] = array();
        if ($is_autoupdate)
            $options['paginationInput']['entriesPerPage'] = $this->config('entries_per_page_update');
        else
            $options['paginationInput']['entriesPerPage'] = $this->config('entries_per_page');
        $options['paginationInput']['pageNumber'] = 1;

        // Sorting
        $options['sortOrder'] = $this->config('sort_order');

        $category_id = $this->config('category_id');
        // Category searches are not supported on the eBay Italy site (global ID EBAY-IT) at this time.
        if ($category_id && $global_id != 'EBAY-IT')
        {
            // up to 3 categs
            $category_id = preg_split("/[\s,]+/", $category_id, 3, PREG_SPLIT_NO_EMPTY);
            $options['categoryId'] = $category_id;
            unset($category_id);
        }

        // Filters
        // @link https://developer.ebay.com/devzone/finding/callref/types/ItemFilterType.html
        $options['itemFilter'] = array();
        $filter = array();

        // from autoblog
        if (isset($query_params['product_condition']))
        {
            //@link: https://developer.ebay.com/DevZone/finding/CallRef/Enums/conditionIdList.html
            $conditions = array(
                'new' => 1000,
                'used' => 3000,
                'refurbished' => 2000,
                'seller_refurbished' => 2500,
                'new_other' => 1500,
                'for_parts' => 7000,
            );

            if (isset($conditions[$query_params['product_condition']]))
            {
                $filter['name'] = 'Condition';
                $filter['value'] = $conditions[$query_params['product_condition']];
                $options['itemFilter'][] = $filter;
            }
        } elseif ($this->config('condition'))
        {
            $filter['name'] = 'Condition';
            $filter['value'] = explode(',', rtrim($this->config('condition'), ','));
            $options['itemFilter'][] = $filter;
        }

        // BestOfferOnly
        if ((bool) $this->config('best_offer_only'))
        {
            $filter['name'] = 'BestOfferOnly';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // AvailableTo
        if ($this->config('available_to'))
        {
            $filter['name'] = 'AvailableTo';
            $filter['value'] = $this->config('available_to');
            $options['itemFilter'][] = $filter;
        }
        // ExcludeCategory
        if ($this->config('exclude_category'))
        {
            $filter['name'] = 'ExcludeCategory';
            // Up to 25 categories can be specified. 
            $filter['value'] = preg_split("/[\s,]+/", $this->config('exclude_category'), 25, PREG_SPLIT_NO_EMPTY);
            $options['itemFilter'][] = $filter;
        }
        // FeaturedOnly
        if ((bool) $this->config('featured_only'))
        {
            $filter['name'] = 'FeaturedOnly';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // FeedbackScoreMin
        if ($this->config('feedback_score_min'))
        {
            $filter['name'] = 'FeedbackScoreMin';
            $filter['value'] = (int) $this->config('feedback_score_min');
            $options['itemFilter'][] = $filter;
        }
        // FreeShippingOnly
        if ((bool) $this->config('free_shipping_only'))
        {
            $filter['name'] = 'FreeShippingOnly';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // LocalPickupOnly
        if ((bool) $this->config('local_pickup_only'))
        {
            $filter['name'] = 'LocalPickupOnly';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // GetItFastOnly
        if ((bool) $this->config('get_it_fast_only'))
        {
            $filter['name'] = 'HideDuplicateItems';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // HideDuplicateItems
        if ((bool) $this->config('hide_dublicate_items'))
        {
            $filter['name'] = 'HideDuplicateItems';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // ListingType
        if ((bool) $this->config('listing_type'))
        {
            $filter['name'] = 'ListingType';
            if ($this->config('listing_type') == 'FixedPrice,AuctionWithBIN')
                $filter['value'] = array('FixedPrice', 'AuctionWithBIN');
            else
                $filter['value'] = $this->config('listing_type');
            $options['itemFilter'][] = $filter;
        }
        // MaxBids
        if ($this->config('max_bids'))
        {
            $filter['name'] = 'MaxBids';
            $filter['value'] = $this->config('max_bids');
            $options['itemFilter'][] = $filter;
        }
        // MinBids
        if ($this->config('min_bids'))
        {
            $filter['name'] = 'MinBids';
            $filter['value'] = $this->config('min_bids');
            $options['itemFilter'][] = $filter;
        }

        // Max price
        if (!empty($query_params['max_price']))
            $max_price = $query_params['max_price'];
        elseif ($this->config('max_price'))
            $max_price = $this->config('max_price');
        else
            $max_price = 0;
        if ($max_price)
        {
            $filter['name'] = 'MaxPrice';
            $filter['value'] = $max_price;
            $options['itemFilter'][] = $filter;
        }
        // MinPrice
        if (!empty($query_params['min_price']))
            $min_price = $query_params['min_price'];
        elseif ($this->config('min_price'))
            $min_price = $this->config('min_price');
        else
            $min_price = 0;
        if ($min_price)
        {
            $filter['name'] = 'MinPrice';
            $filter['value'] = $min_price;
            $options['itemFilter'][] = $filter;
        }

        // TopRatedSellerOnly
        // The TopRatedSellerOnly item filter is supported for the following sites only:
        // US (EBAY-US), Motors (EBAY-MOTOR), UK (EBAY-GB), IE (EBAY-IE), DE (EBAY-DE), AT (EBAY-AT), and CH (EBAY-CH).
        if ($this->config('top_rated_seller_only') &&
                in_array($global_id, array('EBAY-US', 'EBAY-MOTOR', 'EBAY-GB', 'EBAY-IE',
                    'EBAY-DE', 'EBAY-AT', 'EBAY-CH')))
        {
            $filter['name'] = 'TopRatedSellerOnly';
            $filter['value'] = 1;
            $options['itemFilter'][] = $filter;
        }
        // PaymentMethod
        if ((bool) $this->config('payment_method'))
        {
            $filter['name'] = 'PaymentMethod';
            $filter['value'] = $this->config('payment_method');
            $options['itemFilter'][] = $filter;
        }
        
        // Seller
        if ($this->config('seller'))
        {
            $filter['name'] = 'Seller';
            $filter['value'] = TextHelper::getArrayFromCommaList($this->config('seller'));
            $options['itemFilter'][] = $filter;
        }

        // EndTimeTo
        if ($this->config('end_time_to'))
        {
            //hardcode +10min - 600sec
            $filter['name'] = 'EndTimeFrom';
            $filter['value'] = gmstrftime("%Y-%m-%dT%H:%M:%SZ", time() + 600);
            $options['itemFilter'][] = $filter;


            $filter['name'] = 'EndTimeTo';
            $filter['value'] = gmstrftime("%Y-%m-%dT%H:%M:%SZ", time() + $this->config('end_time_to'));
            $options['itemFilter'][] = $filter;
        }

        // advanced search operators
        // (e.g., ( ), -, +, *, or @)
        $keywords = $keyword;
        // allow -word        
        //$keywords = str_replace('-', ' ', $keywords);
        $keywords = trim(preg_replace('/[\(\)\+\*\@\,]/', '', $keywords));
        $keywords = preg_split('/\s+/', $keywords);
        if ($this->config('search_logic') == 'OR')
            $keywords = '(' . join(',', $keywords) . ')';
        elseif ($this->config('search_logic') == 'EXACT')
            $keywords = join(',', $keywords);
        else
            $keywords = join(' ', $keywords); //AND - default

        $results = $this->getEbayClientFinding()->findItemsAdvanced($keywords, $options);

        if (!is_array($results) || !isset($results['searchResult']['item']))
            return array();

        if (!isset($results['searchResult']['item'][0]))
            $results['searchResult']['item'] = array($results['searchResult']['item']);
        return $this->prepareResults($results['searchResult']['item']);
    }

    public function doRequestItems(array $items)
    {
        $params = array();
        $params['IncludeSelector'] = 'Details';
        foreach ($items as $item)
        {
            $all_item_ids[] = $item['unique_id'];
        }
        // You can provide a maximum of 20 item IDs
        // Paging Through Results
        $total = count($items);
        $pages_count = ceil($total / 20);
        $results = array();

        for ($i = 0; $i < $pages_count; $i++)
        {
            $item_ids = array_slice($all_item_ids, 20 * $i, 20);

            $r = $this->getEbayClientShopping()->getMultipleItems($item_ids, $params);
            if (!$r || !isset($r['Item']))
                throw new \Exception('ItemLookup request error.');
            if ($r['Item'] && !isset($r['Item'][0]))
                $r['Item'] = array($r['Item']);
            $results = array_merge($results, $r['Item']);
        }

        $i = 0;
        foreach ($items as $key => $item)
        {
            $r = $results[$i];

            if (empty($r))
            {
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;
                $i++;
                continue;
            }

            if ($item['unique_id'] != $r['ItemID'])
                continue;
            $items[$key]['price'] = (float) $r['ConvertedCurrentPrice']['Value'];
            if (isset($r['DiscountPriceInfo']) && isset($r['DiscountPriceInfo']['OriginalRetailPrice']))
                $items[$key]['priceOld'] = (float) $r['DiscountPriceInfo']['OriginalRetailPrice']['Value'];

            if ($r['ListingStatus'] == 'Active')
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_IN_STOCK;
            else
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;

            // stock status fix
            if (isset($r['Quantity']) && $r['Quantity'] == 0)
                $items[$key]['stock_status'] = ContentProduct::STOCK_STATUS_OUT_OF_STOCK;

            $items[$key]['extra']['SellingStatus']['bidCount'] = (int) $r['BidCount'];
            $i++;
        }
        return $items;
    }

    private function prepareResults($results)
    {
        $global_id = $this->config('global_id');
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentProduct;
            $content->unique_id = $r['itemId'];
            $content->merchant = 'eBay';
            $content->domain = EbayConfig::getDomainByGlobalId($global_id);
            $content->stock_status = ContentProduct::STOCK_STATUS_IN_STOCK;

            $content->title = strip_tags($r['title']);

            if (!$this->config('tracking_id'))
                $content->orig_url = $r['viewItemURL'];

            // epn/slimlinks/viglink?
            $content->url = $this->makeAffLink($r['viewItemURL']);

            // The listing's current price converted to the currency of the site 
            // specified in the find request (globalId). 

            if (is_array($r['sellingStatus']['convertedCurrentPrice']))
                $content->price = (float) $r['sellingStatus']['convertedCurrentPrice'][0];
            elseif (isset($r['sellingStatus']['convertedCurrentPrice']))
                $content->price = (float) $r['sellingStatus']['convertedCurrentPrice'];

            $content->currencyCode = EbayConfig::getCurrencyByGlobalId($global_id);
            $content->currency = TextHelper::currencyTyping($content->currencyCode);
            if (isset($r['discountPriceInfo']) && isset($r['discountPriceInfo']['originalRetailPrice']))
                $content->priceOld = (float) $r['discountPriceInfo']['originalRetailPrice'];
            if (!empty($r['pictureURLLarge']))
                $content->img = $r['pictureURLLarge'];
            elseif (!empty($r['PictureURLSuperSize']))
                $content->img = $r['PictureURLSuperSize'];
            elseif (!empty($r['galleryURL']))
            {
                $img = $r['galleryURL'];
                $img = str_replace('/140.jpg', '.jpg', $img);
                $img = str_replace('/80.jpg', '.jpg', $img); // in locale
                $img = str_replace('/m/', '/d/l225/m/', $img);
                $content->img = $img;
            }
            $content->img = str_replace('http://', 'https://', $content->img);

            if (isset($r['primaryCategory']))
                $content->category = $r['primaryCategory']['categoryName'];

            // Все остальные данные ложим в extra
            $extra = new ExtraDataEbay;

            if (isset($r['condition']))
                $extra->conditionDisplayName = $r['condition']['conditionDisplayName'];

            ExtraData::fillAttributes($extra, $r);
            $extra->listingInfo = new ExtraEbayListingInfo;
            ExtraData::fillAttributes($extra->listingInfo, $r['listingInfo']);

            $extra->sellingStatus = new ExtraEbaySellingStatus;
            ExtraData::fillAttributes($extra->sellingStatus, $r['sellingStatus']);

            $extra->shippingInfo = new ExtraEbayShippingInfo;
            ExtraData::fillAttributes($extra->shippingInfo, $r['shippingInfo']);

            if (isset($r['shipToLocations']) && is_array($r['shipToLocations']))
                $extra->shippingInfo->shipToLocations = join(', ', $r['shipToLocations']);

            // endTime
            $extra->listingInfo->endTime = str_replace('T', ' ', $extra->listingInfo->endTime);

            //для ebay DE, класс энергосбережения
            if (isset($r['eekStatus']))
            {
                $extra->eekStatus = is_array($r['eekStatus']) ? join(', ', $r['eekStatus']) : $r['eekStatus'];
            } else
                $extra->eekStatus = '';

            // Сохраним endTime по Гринвичу
            $extra->listingInfo->endTimeGmt = $extra->listingInfo->endTime;
            $extra->listingInfo->endTimeGmt = date("d-M-y H:i:s", strtotime(substr($extra->listingInfo->endTimeGmt, 0, -5)));

            // Корректируем endTime в зависимости от филиала ebay
            $extra->listingInfo->endTime = date("M d, Y H:i:s", strtotime(substr($extra->listingInfo->endTime, 0, -5)) + EbayConfig::timeZoneCorrection($global_id));
            $extra->listingInfo->startTime = str_replace('T', ' ', $extra->listingInfo->startTime);
            $extra->listingInfo->startTime = date("M d, Y H:i:s", strtotime(substr($extra->listingInfo->startTime, 0, -5)) + EbayConfig::timeZoneCorrection($global_id));
            $extra->listingInfo->timeZone = EbayConfig::getTimeZone($global_id);

            $content->extra = $extra;
            $data[] = $content;
        }

        // need description?
        if ($this->config('get_description'))
        {
            $data = $this->getItemsDescription($data);
        }
        return $data;
    }

    private function getItemsDescription(array $data)
    {
        $item_ids = array();
        //@todo: You can provide a maximum of 20 item IDs
        foreach ($data as $d)
        {
            $item_ids[] = $d->unique_id;
        }

        $params = array();
        $params['IncludeSelector'] = 'Details,TextDescription';

        $results = $this->getEbayClientShopping()->getMultipleItems($item_ids, $params);
        if (!$results || !isset($results['Item']))
            return $data;

        if (!isset($results['Item'][0]))
            $results['Item'] = array($results['Item']);

        foreach ($results['Item'] as $i => $r)
        {
            if (isset($r['Description']))
                $description = $r['Description'];
            else
                $description = '';
            if ($this->config('description_size'))
                $description = TextHelper::truncateHtml($description, $this->config('description_size'));
            $data[$i]->description = $description;
        }
        return $data;
    }

    private function getEbayClientFinding()
    {
        if ($this->api_client_finding === null)
        {
            $app_id = $this->config('app_id');
            $global_id = $this->config('global_id');
            $this->api_client_finding = new EbayFinding($app_id, $global_id, 'xml');
        }
        return $this->api_client_finding;
    }

    private function getEbayClientShopping()
    {
        if ($this->api_client_shopping === null)
        {
            $app_id = $this->config('app_id');
            $global_id = $this->config('global_id');
            $this->api_client_shopping = new EbayShopping($app_id, $global_id, 'json');
        }
        return $this->api_client_shopping;
    }

    private function makeAffLink($url)
    {
        if ($this->config('tracking_id'))
            return $url;

        /**
         * Ebay India and hasoffers
         */
        if ($this->config('ebayin_aff_id') && $this->config('global_id') == 'EBAY-IN')
        {

            $mpre_params = array(
                'aff_source' => 'hasoffers',
                'offer_id' => '19',
            );
            $mpre_url = \add_query_arg($mpre_params, $url);
            $rover_url = 'http://rover.ebay.com/rover/1/4686-203594-43235-92/2?mpre=' . urlencode($mpre_url);
            $aff_url = 'http://ebayindia.go2cloud.org/aff_c?offer_id=19&aff_id=' . urlencode($this->config('ebayin_aff_id')) . '&source=contentegg&url=' . urlencode($rover_url);
            if ($this->config('custom_id'))
                $aff_url .= '&aff_sub=' . urlencode($this->config('custom_id'));

            return $aff_url;
        }

        /**
         *  Skimlinks
         *  @link: http://go.redirectingat.com/doc/
         */
        if ($this->config('skimlinks_id'))
        {
            return 'http://go.redirectingat.com/?'
                    . 'url=' . urlencode($url)
                    . '&id=' . $this->config('skimlinks_id')
                    . '&xs=1';
            //@todo: add sref parameter. This field is also used to detect spam 
            //and it is recommended that it is always passed.
        }

        /**
         *  Viglink
         *  @link: http://support.viglink.com/entries/21981601-How-do-I-wrap-multiple-links-
         */
        if ($this->config('viglink_id'))
        {
            return 'http://redirect.viglink.com?'
                    . 'out=' . urlencode($url)
                    . '&key=' . $this->config('viglink_id');
        }

        if ($this->config('deeplink'))
        {
            return LinkHandler::createAffUrl($url, $this->config('deeplink'));
        }

        //original ebay url
        return $url;
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
