<?php
/**
 * Plugin Name: test plugin for Fb  auto post
 * Plugin URI: 
 * Description: Automatically post WordPress posts to a Facebook page.
 * Version: 1.0.0
 * Author: D Yasuri Hettiarachchi, used chatgpt
 * Author URI: https://danushikay.com/
 */



 define( 'FB_APP_ID', 'sdfsf' );
 define( 'FB_APP_SECRET', 'sdfsdf' );
 define( 'FB_PAGE_ID', 'sdfsdf');


add_action( 'add_meta_boxes', 'auto_post_facebook_meta_box' );

function auto_post_facebook_meta_box() {
    add_meta_box( 'auto-post-facebook', 'Auto Post to Facebook Page', 'auto_post_facebook_meta_box_callback', 'post' );
}

function auto_post_facebook_meta_box_callback( $post ) {
    $auto_post = get_post_meta( $post->ID, '_auto_post_facebook', true );
    ?>
    <label>
        <input type="checkbox" name="_auto_post_facebook" value="1" <?php checked( $auto_post, '1' ); ?>>
        Auto-post to Facebook page
    </label>
    <?php
}

// Save data.
add_action( 'save_post', 'auto_post_facebook_save_meta_box_data' );

function auto_post_facebook_save_meta_box_data( $post_id ) {
    if ( isset( $_POST['_auto_post_facebook'] ) ) {
        update_post_meta( $post_id, '_auto_post_facebook', '1' );
    } else {
        delete_post_meta( $post_id, '_auto_post_facebook' );
    }
}

// Hooked the function
add_action( 'publish_post', 'auto_post_facebook_publish_post', 10, 2 );

function auto_post_facebook_publish_post( $post_ID, $post ) {
    // Check if auto-posting is enabled for this post.
    $auto_post = get_post_meta( $post_ID, '_auto_post_facebook', true );
    if ( $auto_post != '1' ) {
        return;
    }

    
    $access_token = get_facebook_access_token();

    // Make the API call to post to the Facebook page.
    $response = wp_remote_post( "https://graph.facebook.com/v12.0/". FB_PAGE_ID ."/feed", array(
        'body' => array(
            'message' => $post->post_title,
            'link' => get_permalink( $post_ID ),
            'access_token' => $access_token,
        ),
    ) );

    if ( is_wp_error( $response ) ) {
        error_log( "Auto Post to Facebook Page error: ". $response->get_error_message() );
    } else {
        $body = json_decode( wp_remote_retrieve_body( $response ) );
        if ( isset( $body->id ) ) {
            update_post_meta( $post_ID, '_auto_post_facebook_id', $body->id );
        }
    }
}

// Get an access token for the Facebook API.
function get_facebook_access_token() {
    $response = wp_remote_get( "https://graph.facebook.com/v12.0/oauth/access_token", array(
        'body' => array(
            'client_id' => FB_APP_ID,
            'client_secret' => FB_APP_SECRET,
            'grant_type' => 'client_credentials',
        ),
    ) );

  if (is_wp_error( $response ) ) {
    error_log( "Auto Post to Facebook Page error: ". $response->get_error_message() );
    return null;
} else {
    $body = json_decode( wp_remote_retrieve_body( $response ) );
    if ( isset( $body->access_token ) ) {
        return $body->access_token;
    } else {
        error_log( "Auto Post to Facebook Page error: access token not found in API response" );
        return null;
    }
}


/*e

Note that you'll need to replace the `FB_APP_ID`, `FB_APP_SECRET`, and `FB_PAGE_ID` constants with your own Facebook API information, and you may need to adjust the API version number (`v12.0` in this example) depending on the current version of the Facebook API. 

Also, this code assumes that you have already created a Facebook app and a page, and that you have the necessary permissions to post to the page. If you haven't done so already, you'll need to create a Facebook app, obtain an app ID and secret, and create a page access token for your page. You can find more information on how to do this in the Facebook for Developers documentation.
*/