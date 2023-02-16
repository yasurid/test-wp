<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

use Elementor\Widget_Wpsm_Box;
use WP_REST_Request;
use WP_REST_Server;

class Box extends Basic {
	protected $name = 'box';

	protected $attributes = array(
		'type'      => array(
			'type'    => 'string',
			'default' => 'green',
		),
		'float'     => array(
			'type'    => 'string',
			'default' => 'none',
		),
		'textalign' => array(
			'type'    => 'string',
			'default' => 'left',
		),
		'content'   => array(
			'type'    => 'string',
			'default' => 'Box Content',
		),
		'width'     => array(
			'type'    => 'string',
			'default' => 'auto',
		),
		'date'      => array(
			'type'    => 'string',
			'default' => '',
		),
		'takeDate'  => array(
			'type'    => 'boolean',
			'default' => false,
		),
		'label'     => array(
			'type'    => 'string',
			'default' => 'Update',
		),
	);

	protected function render($settings = array()){

		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($settings['content']);
		$content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
		$Old     = array( '<br />', '<br>' );
		$New     = array( '', '' );
		$content = str_replace($Old, $New, $content);

		$label = $settings['takeDate'] ? '<span class="label-info">'.$settings['date'].' '.$settings['label'].'</span>' : '';

		$out = '<div class="wpsm_box '.$settings['type'].'_type '.$settings['float'].'float_box mb30" style="text-align:'.$settings['textalign'].'; width:'.$settings['width'].'"><i></i>'.$label.'<div>
			'.do_shortcode($content).'
			</div></div>';

		return $out;
	}
}
