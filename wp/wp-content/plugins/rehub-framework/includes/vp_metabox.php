<?php
/**
 * Rehub Framework Metabox Functions
 *
 * @package ReHub\Functions
 * @version 1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* load metaboxes */
$post_type_metabox  = rf_locate_template('inc/metabox/post_type.php');
$post_type_side_metabox  = rf_locate_template('inc/metabox/post_type_side.php');
$page_toptable_metabox  = rf_locate_template('inc/metabox/page_toptable.php'); 
$page_topchart_metabox  = rf_locate_template('inc/metabox/page_topchart.php');
$page_option  = rf_locate_template('inc/metabox/page_option.php');
$visual_builder_metabox  = rf_locate_template('inc/metabox/visual_builder.php');

$post_type_metabox_obj = new VP_Metabox($post_type_metabox);
$post_type_metabox_side_obj = new VP_Metabox($post_type_side_metabox);
$page_toptable_metabox_obj = new VP_Metabox($page_toptable_metabox);
$page_topchart_metabox_obj = new VP_Metabox($page_topchart_metabox);
$page_obj = new VP_Metabox($page_option);
$visual_builder_metabox_obj = new VP_Metabox($visual_builder_metabox);

$wooreview_metabox  = rf_locate_template('inc/metabox/woo_review.php');
$wooreview_metabox_obj = new VP_Metabox($wooreview_metabox);