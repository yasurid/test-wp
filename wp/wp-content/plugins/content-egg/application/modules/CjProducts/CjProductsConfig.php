<?php

namespace ContentEgg\application\modules\CjProducts;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * CjProductsConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class CjProductsConfig extends AffiliateParserModuleConfig {

    public function options()
    {
        $options = array(
            'access_token' => array(
                'title' => 'Personal access token <span class="cegg_required">*</span>',
                'description' => __('A Personal Access Token is a unique identification string for your account. You can get it <a target="_blank" href="https://developers.cj.com/account/personal-access-tokens">here</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Personal access token'),
                    ),
                ),
                'section' => 'default',
            ),
            'website_id' => array(
                'title' => 'Website ID <span class="cegg_required">*</span>',
                'description' => __('PID - site id in CJ. Login in your account in CJ and follow "Account -> Websites"', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => __('The field "Website ID" can not be empty.', 'content-egg'),
                    ),
                ),
                'section' => 'default',
            ),
            'dev_key' => array(
                'description' => __('Developer keys have been deprecated. They will continue to work when authenticating with existing APIs, but will not work with future APIs. Please use personal access tokens instead.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),

            'entries_per_page' => array(
                'title' => __('Results', 'content-egg'),
                'description' => __('Number of results for one search query.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates ', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 6,
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
            'advertiser_ids' => array(
                'title' => __('Advertisers', 'content-egg'),
                'description' => __('You can set  Advertiser ID (CID) with comma for search limit only among this advertisers. Set  "joined" for searching only among your advertiser. ', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 'joined',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'low_price' => array(
                'title' => __('Minimal price', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'high_price' => array(
                'title' => __('Maximal price', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'low_sale_price' => array(
                'title' => __('Minimum sale price', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'high_sale_price' => array(
                'title' => __('Maximum sale price', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'serviceable_area' => array(
                'title' => 'Serviceable area',
                'description' => 'Limits the results to a specific set of advertisers’ targeted areas, eg: "US".',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'currency' => array(
                'title' => 'Currency',
                'description' => 'Limits the results to one of the CJ supported tracking currencies.',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'sort_by' => array(
                'title' => __('Sorting', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => 'Relevance',
                    'name' => 'Name',
                    'advertiser-id' => 'Advertiser ID',
                    'advertiser-name' => 'Advertiser name',
                    'currency' => 'Currency',
                    'price' => 'Price',
                    'sale-price' => 'Sale price',
                    'manufacturer-name' => 'Manufacturer Name',
                    'sku' => 'SKU',
                    'upc' => 'UPC',
                ),
                'default' => '',
                'section' => 'default',
            ),
            'sort_order' => array(
                'title' => __('Sorting order', 'content-egg'),
                'description' => '',
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'asc' => 'Ascending ',
                    'desc' => 'Descending',
                ),
                'default' => 'asc',
                'section' => 'default',
            ),
            'manufacturer_name' => array(
                'title' => 'Manufacturer name',
                'description' => 'Limits the results to a particular manufacturer\'s name.',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'manufacturer_sku' => array(
                'title' => 'Manufacturer SKU',
                'description' => 'Limits the results to a particular manufacturer\'s SKU number.',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'advertiser_sku' => array(
                'title' => 'Advertiser SKU',
                'description' => 'Limits the results to a particular advertiser SKU.',
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'section' => 'default',
            ),
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'description_size' => array(
                'title' => __('Trim description', 'content-egg'),
                'description' => __('Description size in characters (0 - do not cut)', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '300',
                'validator' => array(
                    'trim',
                    'absint',
                ),
                'section' => 'default',
            ),
        );
        $parent = parent::options();
        $parent['update_mode']['default'] = 'cron';
        return array_merge($parent, $options);
    }

}
