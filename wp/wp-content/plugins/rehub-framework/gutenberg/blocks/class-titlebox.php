<?php

namespace Rehub\Gutenberg\Blocks;

defined('ABSPATH') OR exit;

use WP_REST_Request;
use WP_REST_Server;

class TitleBox extends Basic {
	protected $name = 'titlebox';

	protected $attributes = array(
		'style' => array(
			'type'    => 'string',
			'default' => '1',
		),
		'title' => array(
			'type'    => 'string',
			'default' => 'Title',
		),
		'text'  => array(
			'type'    => 'string',
			'default' => 'Content',
		),
	);

	protected function render($settings = array()){
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($settings['text']);
		$content = preg_replace('%<p>&nbsp;\s*</p>%', '', $content);
		$Old     = array( '<br />', '<br>' );
		$New     = array( '', '' );
		$content = str_replace($Old, $New, $content);
		if($settings['style'] == 'main') {
			$themeclass = ' rehub-main-color-border';
			$colorclass = 'rehub-main-color';
		} else if($settings['style'] == 'secondary') {
			$themeclass = ' rehub-sec-color-border';
			$colorclass = 'rehub-sec-color';
		} else {
			$themeclass = $colorclass = '';
		}

		// return the url
		return '<div class="wpsm-titlebox mb30 wpsm_style_'.$settings['style'].$themeclass.'"><strong class="'.$colorclass.'">'.$settings['title'].'</strong><div>'.$content.'</div></div>';
	}
}
