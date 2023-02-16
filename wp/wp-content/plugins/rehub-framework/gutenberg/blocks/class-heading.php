<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

use Elementor\Widget_Wpsm_Box;
use WP_REST_Request;
use WP_REST_Server;

class Heading extends Basic {
	protected $name = 'heading';

	protected $attributes = array(
		'level'          => array(
			'type'    => 'number',
			'default' => 2,
		),
		'content'        => array(
			'type'    => 'string',
			'default' => 'Heading',
		),
		'backgroundText' => array(
			'type'    => 'string',
			'default' => '01.',
		),
		'textAlign'      => array(
			'type'    => 'string',
			'default' => 'left',
		),
	);

	protected function render($settings = array()){

		$level = $settings['level'];
		if(!is_numeric($level) || $level < 1 || $level > 6) {
			$level = 2;
		}

		$level = 'h'.$level;

		$wrapperClassAlign = [
			'center' => 'rh-flex-justify-center',
			'left'   => 'rh-flex-justify-start',
			'right'  => 'rh-flex-justify-end',
		];

		$numberClassAlign = [
			'center' => 'text-center',
			'left'   => 'text-left-align',
			'right'  => 'text-right-align',
		];

		$this->add_render_attribute('wrapper', 'class', array(
			'wpsm_heading_number',
			'position-relative',
			'rh-flex-center-align',
			'mb25',
			$wrapperClassAlign[$settings['textAlign']],
		));

		$this->add_render_attribute('number', 'class', array(
			'number',
			'abdfullwidth',
			'width-100p',
			$numberClassAlign[$settings['textAlign']],
		));

		$out = '<div '.$this->get_render_attribute_string('wrapper').'>
            <div '.$this->get_render_attribute_string('number').'>'.$settings['backgroundText'].'</div>
            <div class="wpsm_heading_context position-relative">
            <'.$level.' class="mt0 mb0 ml15 mr15">
			'.$settings['content'].'
			</'.$level.'>
            </div>
			</div>';

		return $out;
	}
}
