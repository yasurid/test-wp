<?php
/**
 * Adds RH Woo Tools settings tab
 */
 
if ( !defined( 'WPINC' ) ) die;

class RH_WC_Settings_Tab_Tools {

    /**
     * Bootstraps the class and hooks required actions & filters.
     */
    public function init() {
        add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::add_settings_tab', 50 );
        add_action( 'woocommerce_settings_tabs_rhtools', __CLASS__ . '::settings_tab' );
        add_action( 'woocommerce_update_options_rhtools', __CLASS__ . '::update_settings' );
    }

    /**
     * Add a new settings tab to the WooCommerce settings tabs array.
     */
    public static function add_settings_tab( $settings_tabs ) {
        $settings_tabs['rhtools'] = __( 'ReHub Tools', 'rh-wctools' );
        return $settings_tabs;
    }

    /**
     * Uses the WooCommerce admin fields API to output settings via the @see woocommerce_admin_fields() function.
     */
    public static function settings_tab() {
        woocommerce_admin_fields( self::get_settings() );
    }

    /**
     * Uses the WooCommerce options API to save settings via the @see woocommerce_update_options() function.
     */
    public static function update_settings() {
        woocommerce_update_options( self::get_settings() );
    }

    /**
     * Get all the settings for this plugin for @see woocommerce_admin_fields() function.
     */
    public static function get_settings() {

		$settings = array();
		
		//Product Tabs setting
		$settings[] = array(
			'title' => __( 'Product Tabs', 'rh-wctools' ),
			'type' => 'title',
			'id' => 'rhwct_options',
		);
		$settings[] = array(
			'title' => __( 'Hide Description tab', 'rh-wctools' ),
			'desc' => __( 'The option disables Description tab on Product page', 'rh-wctools' ),
			'id' => 'rhwct_hide_desc_tab',
			'default' => 'no',
			'type' => 'checkbox',
		);
		$settings[] = array(
			'title' => __( 'Names of the Custom tabs', 'rh-wctools' ),
			'id' => 'rhwct_tab_product_titles',
			'desc' => __( 'List of the Tabs names separated by semicolons.', 'rh-wctools' ),
			'type' => 'text',
			'placeholder' => __( 'Tab1;Tab2;Tab3', 'rh-wctools' ),
		);
		$settings[] = array(
			'title' => __( 'Contents of the Custom tabs', 'rh-wctools' ),
			'id' => 'rhwct_tab_product_contents',
			'type' => 'textarea',
			'placeholder' => 'Tab1 Content.EOT;
Tab2 [shortcode]EOT;
Tab3 <p>content</p>EOT;',
			'css' => 'min-width:50%; height:65px;',
		);
		$settings[] = array(
			'title' => __( 'Order of the Custom tabs', 'rh-wctools' ),
			'id' => 'rhwct_tab_product_orders',
			'type' => 'text',
			'placeholder' => __( '10;20;30', 'rh-wctools' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'rhwct_tools',
		);
		
		//Fake Sold Out
		$settings[] = array(
			'title' => __( 'Fake Soldout block', 'rh-wctools' ),
			'type' => 'title',
			'id' => 'rhwct_options',
		);
		$settings[] = array(
			'title' => __( 'Enable Sold out block', 'rh-wctools' ),
			'desc' => __( 'Enable Sold out block on inner pages', 'rh-wctools' ),
			'id' => 'rhwct_soldout',
			'default' => 'no',
			'type' => 'checkbox',
		);
		$settings[] = array(
			'title' => __( 'Exlude categories', 'rh-wctools' ),
			'desc' => __( 'List of product categories id which you want to exclude from sold out counter. Divided by comma', 'rh-wctools' ),
			'id' => 'rhwct_soldout_ext',
			'default' => '',
			'type' => 'text',
		);
		$settings[] = array(
			'title' => __( 'Include categories', 'rh-wctools' ),
			'desc' => __( 'List of product categories id which you want to include to sold out counter. Divided by comma', 'rh-wctools' ),
			'id' => 'rhwct_soldout_in',
			'default' => '',
			'type' => 'text',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'rhwct_tools',
		);

		//Related Products settings
		$settings[] = array(
			'title' => __( 'Related products', 'rh-wctools' ),
			'type' => 'title',
			'id' => 'rhwct_options',
		);
		$settings[] = array(
			'title' => __( 'Hide Related Products', 'rh-wctools' ),
			'desc' => __( 'The option disables Related Products section on Product page', 'rh-wctools' ),
			'id' => 'rhwct_hide_related_products',
			'default' => 'no',
			'type' => 'checkbox',
		);
		$settings[] = array(
			'title' => __( 'Exlude products', 'rh-wctools' ),
			'desc' => __( 'List product IDs separated by commas where you want to enable Related Products.', 'rh-wctools' ),
			'id' => 'rhwct_excl_related_products',
			'default' => '',
			'type' => 'text',
		);
		$settings[] = array(
			'title' => __( 'Include products', 'rh-wctools' ),
			'desc' => __( 'List product IDs separated by commas where you want to disable Related Products.', 'rh-wctools' ),
			'id' => 'rhwct_incl_related_products',
			'default' => '',
			'type' => 'text',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'rhwct_tools',
		);
		
		//Product Accessories settings
		$settings[] = array(
			'title' => __( 'Product Accessories', 'rh-wctools' ),
			'type' => 'title',
			'id' => 'rhwct_options',
		);
		$settings[] = array(
			'title' => __( 'Enable Accesories', 'rh-wctools' ),
			'id' => 'rhwct_show_accesories',
			'desc'     => __( 'The option enables Accesories tab on Product page', 'rh-wctools' ),
			'default' => 'no',
			'type' => 'checkbox',
		);
		$settings[] = array(
			'title' => __( 'Order of the accessory tab', 'rh-wctools' ),
			'id' => 'rhwct_tab_product_order_accessory',
			'type' => 'number',
			'placeholder' => __( '10', 'rh-wctools' ),
		);
		$settings[] = array(
			'title' => __( 'Title of Accessory Tab', 'rh-wctools' ),
			'desc' => __( 'Place title here', 'rh-wctools' ),
			'id' => 'rhwct_accessory_title',
			'default' => 'Accessories',
			'type' => 'text',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'rhwct_tools',
		);
		
		// UTILS
		$settings[] = array(
			'title' => __( 'Utilities', 'rh-wctools' ),
			'type' => 'title',
			'id' => 'rhwct_options',
		);
/* 		$settings[] = array(
			'title' => __( 'Hide Products cron', 'rh-wctools' ),
			'desc' => __( 'Select an option how often to run the utility below automatically and Save changes.', 'rh-wctools' ),
			'id' => 'rhwct_cron_duplicated_products',
			'type' => 'select',
			'class' => 'wc-enhanced-select',
			'options'  => array(
				'0' => __( 'At once', 'rh-wctools' ),
				'12hrs' => __( 'Every 12 hours', 'rh-wctools' ),
				'24hrs' => __( 'Every 24 hours', 'rh-wctools' ),
				'72hrs' => __( 'Every 72 hours', 'rh-wctools' ),
				'week' => __( 'Every week', 'rh-wctools' ),
				'month' => __( 'Every month', 'rh-wctools' ),
			),
		); */
		$settings[] = array(
			'title' => __( 'Hide Products utility', 'rh-wctools' ),
			'type' => 'button',
			'id' => 'rhwct_hide_duplicate_sku',
			'value' => __( 'Run', 'rh-wctools' ),
			'class' => 'button-secondary woocommerce-run-button',
			'desc' => __( 'The utility will hide products which have identical SKU after Product import.', 'rh-wctools' ),
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id' => 'rhwct_tools',
		);

        return apply_filters( 'wc_settings_rhtools', $settings );
    }
}