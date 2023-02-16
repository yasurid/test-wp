<?php

namespace Rehub\Gutenberg;

defined('ABSPATH') OR exit;

final class Assets {
	private static $instance = null;

	/** @return Assets */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private $style = array();
	private $responsive_style = array();

	protected $is_rest = false;
	protected $is_editor = false;
	protected $is_elementor_editor = false;

	/** @var \stdClass $assets */
	protected $assets = null;

	private function __construct(){
		add_action('enqueue_block_editor_assets', array( $this, 'editor_gutenberg' ));
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ));

		add_action('init', array( $this, 'init' ));

		$this->assets             = new \stdClass();
		$this->assets->path       = __DIR__.'/';
		$this->assets->path_css   = $this->assets->path.'assets/css/';
		$this->assets->path_js    = $this->assets->path.'assets/js/';
		$this->assets->path_image = $this->assets->path.'assets/images/';
		$this->assets->url        = plugins_url('/', __FILE__);
		$this->assets->url_css    = $this->assets->url.'assets/css/';
		$this->assets->url_js     = $this->assets->url.'assets/js/';
		$this->assets->url_image  = $this->assets->url.'assets/images/';
	}

	public function init(){
		$this->is_rest             = defined('REST_REQUEST');
		$this->is_elementor_editor = class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->editor->is_edit_mode();
		$this->is_editor           = $this->is_rest || $this->is_elementor_editor;
	}

	public function admin_enqueue_scripts(){
		// Styles.
		wp_enqueue_style(
			'rehub-blocks-editor',
			$this->assets->url_css.'editor.css',
			array( 'wp-edit-blocks' ),
			filemtime($this->assets->path_css.'editor.css')
		);
	}


	function get_jed_locale_data($domain){
		$translations = get_translations_for_domain($domain);

		$locale = array(
			'' => array(
				'domain' => $domain,
				'lang'   => is_admin() ? get_user_locale() : get_locale(),
			),
		);

		if(!empty($translations->headers['Plural-Forms'])) {
			$locale['']['plural_forms'] = $translations->headers['Plural-Forms'];
		}

		foreach($translations->entries as $msgid => $entry) {
			$locale[$msgid] = $entry->translations;
		}

		return $locale;
	}

	/**
	 * Enqueue Gutenberg block assets for backend editor.
	 */
	function editor_gutenberg(){
		static $loaded = false;
		if($loaded) {
			return;
		}
		$loaded = true;
		wp_enqueue_script(
			'rehub-blocks-editor',
			$this->assets->url_js.'editor.js',
			array(),
			filemtime($this->assets->path_js.'editor.js'),
			true
		);

		$default_attributes = apply_filters('rehub/gutenberg/default_attributes', array());
		wp_localize_script('rehub-blocks-editor','RehubGutenberg', array(
			'blocks' => array(),
			'attributes' => $default_attributes,
		));

		wp_enqueue_style(
			'rehub-blocks-editor',
			$this->assets->url_css.'editor.css',
			array(),
			filemtime($this->assets->path_css.'editor.css')
		);

//		wp_enqueue_style('rhstyle', get_stylesheet_directory_uri().'/style.css', array(), RH_MAIN_THEME_VERSION);
	}
}

