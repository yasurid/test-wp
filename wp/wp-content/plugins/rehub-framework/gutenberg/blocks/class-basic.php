<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

use Elementor\Plugin;
use Rehub\Gutenberg\Blocks\Basic\Inline_Attributes_Trait;
use WP_REST_Request;
use WP_REST_Server;

abstract class Basic {
	use Inline_Attributes_Trait;

	protected static $index = 0;

	protected $render_index = 1;
	protected $name = 'basic';

	protected $is_rest = false;
	protected $is_editor = false;
	protected $is_elementor_editor = false;

	protected $attributes = array();

	protected static $instance = null;

	final public static function instance(){
		static $instance = null;

		if(is_null($instance)) {
			$instance = new static();
		}

		return $instance;
	}

	protected function __construct(){
		add_action('init', array( $this, 'init_handler' ));
		add_filter('rehub/gutenberg/default_attributes', array( $this, 'get_default_attributes' ));

		$this->construct();

	}

	protected function construct(){

	}

	private function __clone(){
	}

	private function __wakeup(){
	}

	function rest_api_init(){
		$namespace = 'rehub/v1/block-render';

		register_rest_route($namespace,
			$this->name,
			array(
				array(
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => array( $this, 'rest_handler' ),
				),
			)
		);
	}

	public function rest_handler(WP_REST_Request $Request){
		$data = array(
			'rendered' => $this->render_block($Request->get_params()),
		);

		return rest_ensure_response($data);
	}

	public function init_handler(){
		register_block_type('rehub/'.$this->name, array(
			'attributes'      => $this->attributes,
			'render_callback' => array( $this, 'render_block' ),
		));

		if(\is_user_logged_in()) {
			add_action('rest_api_init', array( $this, 'rest_api_init' ));
		}
	}

	public function restHandler(WP_REST_Request $Request){
		$data = array(
			'rendered' => $this->render_block($Request->get_params()),
		);

		return rest_ensure_response($data);
	}


	protected function render($settings){
		return '';
	}

	public function render_block($settings){
		$settings = array_merge(
			$this->array_column_ext($this->attributes, 'default', -1),
			is_array($settings) ? $settings : array()
		);
		ob_start();
		$content = $this->render($settings);

		return strlen($content) ? $content : ob_get_clean();
	}

	protected function array_column_ext($array, $columnkey, $indexkey = null){
		$result = array();
		foreach($array as $subarray => $value) {
			if(array_key_exists($columnkey, $value)) {
				$val = $array[$subarray][$columnkey];
			} else if($columnkey === null) {
				$val = $value;
			} else {
				continue;
			}

			if($indexkey === null) {
				$result[] = $val;
			} else if($indexkey == -1 || array_key_exists($indexkey, $value)) {
				$result[($indexkey == -1) ? $subarray : $array[$subarray][$indexkey]] = $val;
			}
		}

		return $result;
	}

	public function get_default_attributes($attributes) {
		$attributes[$this->name] = $this->attributes;

		return $attributes;
	}
}
