<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_posts_load_widget' );

function rehub_posts_load_widget() {
	register_widget( 'rehub_posts_widget' );
}

class rehub_posts_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'posts_widget', 'description' => esc_html__('A widget that displays custom posts list. Use only in sidebar!', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_posts_widget' );
        parent::__construct( 'rehub_posts_widget', esc_html__('ReHub: Posts List', 'rehub-framework'), $widget_ops, $control_ops);
    }

/**
 * How to display the widget on the screen.
 */
function widget( $args, $instance ) {
	extract( $args );

	/* Our variables from the widget settings. */
	$title = apply_filters('widget_title', $instance['title'] );
	$categories = $instance['categories'];
	$sortby = $instance['sortby'];
	$cpt = (!empty($instance['cpt'])) ? $instance['cpt'] : '';
	$number = $instance['number'];
	$post_type = $instance['post_type'];
	if( !empty($instance['dark']) ) $color = 'dark';
	else $color = '';	

	if($sortby == 'this_week') {
	if(!function_exists('rh_filter_where')){
		function rh_filter_where($where = '') {
			//posts in the last 7 days
			$where .= " AND post_date > '" . date('Y-m-d', strtotime('-7 days')) . "'";
			return $where;
		}
	}
	add_filter('posts_where', 'rh_filter_where');
	} elseif($sortby == 'this_month') {
	if(!function_exists('rh_filter_where')){
		function rh_filter_where($where = '') {
			//posts in the last 30 days
			$where .= " AND post_date > '" . date('Y-m-d', strtotime('-30 days')) . "'";
			return $where;
		}
	}
	add_filter('posts_where', 'rh_filter_where');
	} elseif($sortby == 'three_month') {
	if(!function_exists('rh_filter_where')){
		function rh_filter_where($where = '') {
			//posts in the last 30 days
			$where .= " AND post_date > '" . date('Y-m-d', strtotime('-90 days')) . "'";
			return $where;
		}
	}
	add_filter('posts_where', 'rh_filter_where');
	}
	
	if($post_type == 'all') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories);
	elseif($post_type == 'regular') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories, 'meta_key' => 'rehub_framework_post_type', 'meta_value' => 'regular'); 
	elseif($post_type == 'video') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories, 'meta_key' => 'rehub_framework_post_type', 'meta_value' => 'video');
	elseif($post_type == 'gallery') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories, 'meta_key' => 'rehub_framework_post_type', 'meta_value' => 'gallery');
	elseif($post_type == 'review') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories, 'meta_key' => 'rehub_framework_post_type', 'meta_value' => 'review'); 
	elseif($post_type == 'music') :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories, 'meta_key' => 'rehub_framework_post_type', 'meta_value' => 'music');
	else :
		$query = array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1, 'cat' => $categories);
	endif;

	if($cpt) {$query['post_type']=$cpt;}

	if($sortby == 'random') {$query['orderby'] = 'rand';}

	$query['no_found_rows'] = 1;
	
	global $post;
	$loop = new WP_Query($query);
	/* Before widget (defined by themes). */
	echo ''.$before_widget;
	
	if ($loop->have_posts()) :

	/* Display the widget title if one was input (before and after defined by themes). */
	if ( $title )
		echo '<div class="title">' . $title . '</div>';

	?>
	<div class="color_sidebar<?php if ($color == 'dark') :?> dark_sidebar<?php endif ;?>">
		<div class="tabs-item clearfix">
		<?php  while ($loop->have_posts()) : $loop->the_post(); ?>	
			<div class="clearfix">
	            <figure><a href="<?php the_permalink();?>"><?php wpsm_thumb ('med_thumbs') ?></a></figure>
	            <div class="detail">
		            <h5 class="mt0"><a href="<?php the_permalink();?>"><?php the_title();?></a></h5>

		            <?php if ('post' == get_post_type($post->ID)) :?>
	            	<div class="post-meta">
	              		<?php $category = get_the_category($post->ID); $first_cat = $category[0]->term_id;?>
	                	<?php meta_small( false, $first_cat, true ); ?>
	                </div>
	            	<?php else:?>
	            		<?php $taxarray = get_post_taxonomies($post->ID); ?>
	            		<?php if (!empty($taxarray)):?>
	            			<div class="post-meta">
	            			<?php foreach ($taxarray as $tax) {
	            				$term_list = get_the_term_list( $post->ID, $tax, '<span class="'.$tax.'_meta">', ', ', '</span>' );
	    						echo ''.$term_list;
	            			}?>
	            			</div>
	            		<?php endif;?>	
	            	<?php endif;?>

		            <?php rehub_format_score('small') ?>
	            </div>
            </div>	
		<?php endwhile; ?>
		</div>
	</div>	
	<?php wp_reset_query(); ?>
	<?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-framework'); ?>
	<?php endif; ?>
	<?php remove_filter( 'posts_where', 'rh_filter_where' ); ?>		
	<?php

	/* After widget (defined by themes). */
	echo ''.$after_widget;
}


	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['categories'] = $new_instance['categories'];
		$instance['sortby'] = $new_instance['sortby'];
		$instance['number'] = strip_tags( $new_instance['number'] );
		$instance['post_type'] = $new_instance['post_type'];
		$instance['dark'] = (!empty($new_instance['dark'])) ? strip_tags( $new_instance['dark'] ) : '';
		$instance['cpt'] = strip_tags( $new_instance['cpt'] );		

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => esc_html__('Latest Posts', 'rehub-framework'), 'number' => 5, 'categories' => '', 'sortby' => 'all_time', 'post_type' => 'all', 'dark' => '', 'cpt' => '');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
	

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'title' ); ?>"><?php esc_html_e('Title of widget:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'title' ); ?>" name="<?php echo ''.$this->get_field_name( 'title' ); ?>" value="<?php echo ''.$instance['title']; ?>"  />
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('categories'); ?>"><?php esc_html_e('Filter by Category:', 'rehub-framework'); ?></label> 
		<select id="<?php echo ''.$this->get_field_id('categories'); ?>" name="<?php echo ''.$this->get_field_name('categories'); ?>" class="widefat categories" style="width:100%;">
			<option value='all' <?php if ('all' == $instance['categories']) echo 'selected="selected"'; ?>><?php esc_html_e('All categories', 'rehub-framework'); ?></option>
			<?php $categories = get_categories('hide_empty=0&depth=1&type=post'); ?>
			<?php foreach($categories as $category) { ?>
			<option value='<?php echo (int)$category->term_id; ?>' <?php if ($category->term_id == $instance['categories']) echo 'selected="selected"'; ?>><?php echo ''.$category->cat_name; ?></option>
			<?php } ?>
		</select>
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('sortby'); ?>"><?php esc_html_e('Posts sort by:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('sortby'); ?>" name="<?php echo ''.$this->get_field_name('sortby'); ?>" style="width:100%;">
			<option value='all_time' <?php if ( 'all_time' == $instance['sortby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('all time', 'rehub-framework');?></option>
			<option value='this_week' <?php if ( 'this_week' == $instance['sortby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('this week', 'rehub-framework');?></option>
			<option value='this_month' <?php if ( 'this_month' == $instance['sortby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('this month', 'rehub-framework');?></option>
			<option value='three_month' <?php if ( 'three_month' == $instance['sortby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('last 3 month', 'rehub-framework');?></option>
			<option value='random' <?php if ( 'random' == $instance['sortby'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('random', 'rehub-framework');?></option>
		</select>
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'number' ); ?>"><?php esc_html_e('Number of posts to show:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'number' ); ?>" name="<?php echo ''.$this->get_field_name( 'number' ); ?>" value="<?php echo ''.$instance['number']; ?>" size="3" />
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('post_type'); ?>"><?php esc_html_e('Post Format:', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('post_type'); ?>" name="<?php echo ''.$this->get_field_name('post_type'); ?>" style="width:100%;">
			<option value="all" <?php if ( 'all' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('all', 'rehub-framework');?></option>
			<option value="regular" <?php if ( 'regular' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('regular', 'rehub-framework');?></option>
			<option value="video" <?php if ( 'video' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('video', 'rehub-framework');?></option>
			<option value="gallery" <?php if ( 'gallery' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('gallery', 'rehub-framework');?></option>
			<option value="review" <?php if ( 'review' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('review', 'rehub-framework');?></option>
			<option value="music" <?php if ( 'music' == $instance['post_type'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('music', 'rehub-framework');?></option>
		</select>
		</p>

		<p>
			<label for="<?php echo ''.$this->get_field_id( 'cpt' ); ?>"><?php esc_html_e('Post type (optional)', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'cpt' ); ?>" name="<?php echo ''.$this->get_field_name( 'cpt' ); ?>" value="<?php echo ''.$instance['cpt']; ?>" size="3" />
		</p>


		<p>
			<label for="<?php echo ''.$this->get_field_id( 'dark' ); ?>"><?php esc_html_e('Dark Skin ?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'dark' ); ?>" name="<?php echo ''.$this->get_field_name( 'dark' ); ?>" value="true" <?php if( $instance['dark'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>		


	<?php
	}
}

?>