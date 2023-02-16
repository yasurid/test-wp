<?php

namespace Rehub\Gutenberg;

use WP_REST_Request;
use WP_REST_Server;

defined('ABSPATH') OR exit;

class REST {
	private $rest_namespace = 'rehub/v2/';


	private static $instance = null;

	/** @return Assets */
	public static function instance(){
		if(is_null(static::$instance)) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct(){
		add_action('rest_api_init', array( $this, 'action_rest_api_init_trait' ));
	}

	public function action_rest_api_init_trait(){
//		if(!((is_user_logged_in() && is_admin()))) {
//			return;
//		}

		register_rest_route($this->rest_namespace.'posts',
			'/get',
			array(
				array(
					'methods'  => WP_REST_Server::CREATABLE,
//					'permission_callback' => array( Settings::class, 'is_user_can' ),
					'callback' => array( $this, 'rest_get_posts' ),
				)
			)
		);

	}

	public function rest_get_posts(WP_REST_Request $request){
		$params    = array_merge(
			array(
				's'         => '',
				'include'   => '',
				'exclude'   => '',
				'page'      => 1,
				'post_type' => 'post',
			), $request->get_params()
		);
		$isSelect2 = ($request->get_param('typeQuery') === 'select2');

		$args = array(
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'post_type'      => $params['post_type'],
			'paged'          => $params['page'],
		);

		if(!empty($params['s'])) {
			$args['s'] = $params['s'];
		}
		if(!empty($params['include'])) {
			$args['post__in'] = is_array($params['include']) ? $params['include'] : array( $params['include'] );
		}
		if(!empty($params['exclude'])) {
			$args['post__not_in'] = is_array($params['exclude']) ? $params['exclude'] : array( $params['exclude'] );
		}

		$response_array = array();
		$keys           = $isSelect2 ?
			[ 'label' => 'text', 'value' => 'id' ] :
			[ 'label' => 'label', 'value' => 'value' ];

		$posts = new \WP_Query($args);
		if($posts->post_count > 0) {
			/* @var \WP_Post $gallery */
			foreach($posts->posts as $_post) {
				$response_array[] = array(
					$keys['label'] => !empty($_post->post_title) ? $_post->post_title : __('No Title', ''),
					$keys['value'] => $_post->ID,
				);
			}
		}
		wp_reset_postdata();

		$return = array(
			'results'    => $response_array,
			'pagination' => array(
				'more' => $posts->max_num_pages >= ++$params['page'],
			)
		);

		return rest_ensure_response($return);
	}
}
