<?php
/*
 * Plugin Name: ID24 Social Sharing
 * Plugin URI: 
 * Plugin Description: Demo plug-in for #ID24 and #GAAD
 * Version: 1.0
 * Author: Joe Dolson
 * Author URI: http://www.joedolson.com
 */

/*
 * Declare the text domain used for internationalization of plug-in.
 *
 * @param $domain String required Unique identifier for translated strings.
 * @param $abs_rel_path DEPRECATED
 * @param $plugin_rel_path Relative path to storage of translation files.
 *
 */
load_plugin_textdomain( 'id24-social-sharing', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

/*
 * Require the functions that display and set settings.
 */
require_once( 'inc/settings.php' );

/*
 * Get the post data that will be sent to social sharing pages.
 * 
 * @param integer $post_ID ID of the current post.
 *
 * @return array of post data for use in sharing.
 */
function id24_post_information( $post_ID ) {
	$data = array();
	$data['title']     = get_the_title( $post_ID ); 
	$data['url']       = get_permalink( $post_ID );
	$image_ID          = get_post_thumbnail_id( $post_ID );
	$image             = wp_get_attachment_image_src( $image_ID, 'large' );
	$data['image_url'] = $image[0];
	$data['image_alt'] = get_post_meta( $image_ID, '_wp_attachment_image_alt', true );
	
	return $data;
}

/* 
 * Generate the URLs used to post data to services.
 * 
 * @param integer $post_ID of current post
 * 
 * @return array of URLs for posting to each service.
 */
function id24_create_urls( $post_ID ) {
	$data      = id24_post_information( $post_ID );	
	$twitter   = "https://twitter.com/intent/tweet?text=" . urlencode( $data['title'] . ' ' . $data['url'] );
	$facebook  = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode( $data['url'] );
	$google    = "https://plus.google.com/share?url=" . urlencode( $data['url'] );
	if ( esc_url( $data['image_url'] ) && $data['image_alt'] != '' ) {
		$pinterest = "https://pinterest.com/pin/create/button/?url=" . urlencode( $data['url'] ) . "&media=" . urlencode( $data['image_url'] ) . "&description=" . urlencode( $data['image_alt'] );
	} else {
		$pinterest = false;
	}
	
	return apply_filters( 'id24_social_service_links', array( 
		'twitter'   => $twitter,
		'facebook'  => $facebook,
		'google'    => $google,
		'pinterest' => $pinterest
	), $data );
}

/*
 * Generate the HTML links using URLs.
 *
 * @param integer $post_ID of current post
 *
 * @return string block of HTML links.
 */
function id24_create_links( $post_ID ) {
	$urls = id24_create_urls( $post_ID );
	$html = '';
	
	$settings  = get_option( 'id24_settings' );
	$enabled   = ( isset( $settings['enabled'] ) ) ? $settings['enabled'] : array( 'twitter' => 'on', 'facebook' => 'on', 'google' => 'on', 'pinterest' => 'on' );

	foreach ( $urls as $service => $url ) {
		$is_enabled = in_array( $service, array_keys( $enabled ) );
		if ( $url && $is_enabled ) {
			$html .= "
					<div class='id24-link $service'>
						<a href='" . esc_url( $url ) . "' rel='nofollow external' aria-describedby='description-$service'>
							<span class='id24-icon $service' aria-hidden='true'>" . ucfirst( $service ) . "</span>
						</a>
						<span class='description' role='tooltip' id='description-$service'>
							" . __( 'Share this post' ) . "
						</span>
					</div>";
		}
	}
	
	return "<div class='id24-links'>" . $html . "</div>";
}

/*
 * Fetch HTML for links and wrap in a container. Add heading and ARIA landmark role.
 *
 * @param integer $post_ID of current post.
 *
 * @return full HTML block.
 */
function id24_social_block( $post_ID ) {
	$links = id24_create_links( $post_ID );
	
	$html = "
			<nav aria-labelledby='id24-social-sharing'>
				<h3 id='id24-social-sharing'>" . __( 'Share This Post', 'id24-social-sharing' ) . "</h3>			
				<div class='id24-social-share'>				
					$links
				</div>
			</nav>";
	
	return $html;
}
/*
 * Use WordPress filter 'the_content' to add sharing links into post content.
 *
 * @param $content The current content of the post.
 * 
 * @return $content The previous content of the post plus social sharing links.
 */
add_filter( 'the_content', 'id24_post_content' );
function id24_post_content( $content ) {
	global $post;
	$post_ID = $post->ID;
	if ( is_main_query() && in_the_loop() ) {
		$id24_social = id24_social_block( $post_ID );
		$content = $content . $id24_social;
	}
	
	return $content;
}

/*
 * Register custom stylesheet for ID24 social sharing.
 */
add_action( 'wp_enqueue_scripts', 'id24_register_styles' );
function id24_register_styles() {
	wp_register_style( 'id24-icomoon', plugins_url( 'fonts/icomoon.css', __FILE__ ) );
	if ( !is_admin() ) {
		wp_enqueue_style( 'id24-social-share', plugins_url( 'css/id24.css', __FILE__ ), array( 'dashicons', 'id24-icomoon' ) );
	}
}
