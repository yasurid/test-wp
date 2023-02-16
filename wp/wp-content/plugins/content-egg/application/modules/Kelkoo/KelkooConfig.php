<?php

namespace ContentEgg\application\modules\Kelkoo;

use ContentEgg\application\components\AffiliateParserModuleConfig;

/**
 * KelkooConfig class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2018 keywordrush.com
 */
class KelkooConfig extends AffiliateParserModuleConfig {

    public function options()
    {
        $optiosn = array(
            'trackingId' => array(
                'title' => 'Tracking ID <span class="cegg_required">*</span>',
                'description' => __('Before you can use it you must obtain a Tracking Id from <a target="_blank" href="https://partner.kelkoo.com/protected/ecommerceServices">Kelkoo</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Tracking ID'),
                    ),
                ),
            ),
            'affiliateKey' => array(
                'title' => 'Affiliate Key <span class="cegg_required">*</span>',
                'description' => __('Before you can use it you must obtain a Affiliate Key from <a target="_blank" href="https://partner.kelkoo.com/protected/ecommerceServices">Kelkoo</a>.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Affiliate Key'),
                    ),
                ),
            ),
            'region' => array(
                'title' => __('Region <span class="cegg_required">*</span>', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('- Choose your region -', 'content-egg'),
                    'at' => 'Austria',
                    'be' => 'Belgium (fr)',
                    'nb' => 'Belgium (nl)',
                    'br' => 'Brazil',
                    'cz' => 'Czech Republic',
                    'dk' => 'Denmark',
                    'fi' => 'Finland',
                    'fr' => 'France',
                    'de' => 'Germany',
                    'ie' => 'Ireland',
                    'it' => 'Italy',
                    'mx' => 'Mexico',
                    'nl' => 'Netherlands',
                    'no' => 'Norway',
                    'pl' => 'Poland',
                    'pt' => 'Portugal',
                    'ru' => 'Russia',
                    'es' => 'Spain',
                    'se' => 'Sweden',
                    'ch' => 'Switzerland',
                    'uk' => 'United Kingdom',
                    'us' => 'United States',
                ),
                'default' => '',
                'validator' => array(
                    'trim',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'required'),
                        'when' => 'is_active',
                        'message' => sprintf(__('The field "%s" can not be empty.', 'content-egg'), 'Region'),
                    ),
                ),
            ),
            'entries_per_page' => array(
                'title' => __('Results', 'content-egg'),
                'description' => __('Number of results for one search query.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 10,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'entries_per_page_update' => array(
                'title' => __('Results for updates', 'content-egg'),
                'description' => __('Number of results for automatic updates and autoblogging.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => 6,
                'validator' => array(
                    'trim',
                    'absint',
                    array(
                        'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                        'arg' => 100,
                        'message' => sprintf(__('The field "%s" can not be more than %d.', 'content-egg'), 'Results', 100),
                    ),
                ),
            ),
            'sort' => array(
                'title' => __('Sorting', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    'default_ranking' => __('Ranking', 'content-egg'),
                    'price_ascending' => __('Price ascending', 'content-egg'),
                    'price_descending' => __('Price descending', 'content-egg'),
                    'totalprice_ascending' => __('Total price ascending', 'content-egg'),
                    'totalprice_descending' => __('Total price descending', 'content-egg'),
                ),
                'default' => 'default_ranking',
            ),
            'logicalType' => array(
                'title' => __('Logical type', 'content-egg'),
                'description' => __('For a query with several terms.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Default', 'content-egg'),
                    'AND' => __('all the offers matching term 1 AND term 2', 'content-egg'),
                    'OR' => __('all the offers matching term 1 OR term 2', 'content-egg'),
                ),
                'default' => '',
            ),
            'automaticOr' => array(
                'title' => __('Automatic OR', 'content-egg'),
                'description' => __('Automatic search with a OR instead of AND if there is no results with AND query', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'mobileFriendly' => array(
                'title' => __('Mobile friendly', 'content-egg'),
                'description' => __('Response will return mobile compliant offers only', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'boostMobileResults' => array(
                'title' => __('Boost mobile results', 'content-egg'),
                'description' => __('Response will return mobile friendly offers at the top of the resultset', 'content-egg') .
                '<p class="description">' . __('Offers from merchants that are not considered mobile friendly will also be returned but below those which are mobile friendly.', 'content-egg') . '</p>',
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
            'price_min' => array(
                'title' => __('Minimal price', 'content-egg'),
                'description' => __('Example, 8.99', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'price_max' => array(
                'title' => __('Maximal price', 'content-egg'),
                'description' => __('Example, 98.50', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
                'metaboxInit' => true,
            ),
            'rebatePercentage' => array(
                'title' => __('Rebate percentage', 'content-egg'),
                'description' => __('When set to 30 for example, the response will return offers that have a sale price discounted by 30% or more.', 'content-egg'),
                'callback' => array($this, 'render_dropdown'),
                'dropdown_options' => array(
                    '' => __('Any', 'content-egg'),
                    '5%' => '5%',
                    '10%' => '10%',
                    '15%' => '15%',
                    '20%' => '20%',
                    '25%' => '25%',
                    '30%' => '30%',
                    '35%' => '35%',
                    '40%' => '40%',
                    '45%' => '45%',
                    '50%' => '50%',
                    '60%' => '60%',
                    '70%' => '70%',
                    '80%' => '80%',
                    '90%' => '90%',
                    '95%' => '95%',
                ),
                'default' => '',
                'metaboxInit' => true,
            ),
            'category' => array(
                'title' => 'Category',
                'description' => __('The category to search in. It is the category ID.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
            ),
            'merchantId' => array(
                'title' => __('Merchant ID', 'content-egg'),
                'description' => __('Limit the search to a specific merchant.', 'content-egg'),
                'callback' => array($this, 'render_input'),
                'default' => '',
                'validator' => array(
                    'trim',
                ),
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
            'save_img' => array(
                'title' => __('Save images', 'content-egg'),
                'description' => __('Save images on server', 'content-egg'),
                'callback' => array($this, 'render_checkbox'),
                'default' => false,
                'section' => 'default',
            ),
        );

        $parent = parent::options();
        /**
         * @link: https://www.kelkoogroup.com/kelkoo-customer-service/kelkoo-developer-network/shopping-services/samples/traffic-improvement/
         * You should not cache requests more than 4 hours in order to assure freshness even 
         * if the TimeToLive of the GO URL is set to 7 days.
         */
        $parent['ttl_items']['validator'] = array(
            'trim',
            'absint',
            array(
                'call' => array('\ContentEgg\application\helpers\FormValidator', 'less_than_equal_to'),
                'arg' => 604800,
                'message' => sprintf(__('The field "%s" can\'t be more than %d.', 'content-egg'), __('Price update', 'content-egg'), 604800),
            ),
        );
        return array_merge($parent, $optiosn);
    }

}
