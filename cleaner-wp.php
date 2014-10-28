<?php
/**
 * Plugin Name: Cleaner WP
 * Plugin URI: https://github.com/akozoubsky/cleaner-wp
 * Description: Clean WordPress Features and Styles. 
 * Version: 0.0.1
 * Author: Alexandre Kozoubsky
 * Author URI: http://alexandrekozoubsky.com
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License as published by the Free Software Foundation; either version 2 of the License, 
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write 
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 * 
 * @package    CleanerWP
 * @version    0.0.1
 * @author     Alexandre Kozoubsky <alexandre@alexandrekozoubsky.com>
 * @copyright  Copyright (c) 2014 - 2015, Alexandre Kozoubsky
 * @link       https://github.com/akozoubsky/cleaner-wp
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Queries
 */
  
//change amount of posts on the search page - set here to 100
function wpfme_search_results_per_page( $query ) {
	global $wp_the_query;
	if ( ( ! is_admin() ) && ( $query === $wp_the_query ) && ( $query->is_search() ) ) {
	$query->set( 'wpfme_search_results_per_page', 100 );
	}
	return $query;
}
add_action( 'pre_get_posts',  'wpfme_search_results_per_page'  );

/**
 * Output
 */
 
/* Add classes to the tag body. Usefull with CSS in yout theme. */	
add_filter( 'body_class', 'clwp_body_class' ); 
function clwp_body_class( $classes ) {
	
	if ( get_header_image() )
		$classes[] = 'display-header-image'; /* Post or page with header image. */
		
	if ( get_background_image() )
		$classes[] = 'display-background-image'; /* Post or page with background image. */
		
	if ( has_nav_menu( 'primary' ) )
		$classes[] = 'display-primary-menu'; /* Primary Menu is activated. */

	return $classes;
} 
 
/* Adds a has_sidebar class to the body if there's a sidebar.  */
function wpfme_has_sidebar($classes) {
    if (is_active_sidebar('sidebar')) {
        // add 'class-name' to the $classes array
        $classes[] = 'has_sidebar';
    }
    // return the $classes array
    return $classes;
}
add_filter('body_class','wpfme_has_sidebar');

// Stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
function wpfme_remove_img_ptags($content){
	return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
}
add_filter('the_content', 'wpfme_remove_img_ptags');

// Set a maximum width for Oembedded objects
if ( ! isset( $content_width ) )
$content_width = 1024;

// Stop users creating Galleries
add_action( 'admin_head_media_upload_gallery_form', 'mfields_remove_gallery_setting_div' );
    if( !function_exists( 'mfields_remove_gallery_setting_div' ) ) {
            function mfields_remove_gallery_setting_div() {
            print '
<style type="text/css" id="stop-users-creating-galleries-css">
#gallery-settings *{
display:none;
}
</style>';
        }
    }

	/**
	 * Output - JQuery + CSS
	 */
	add_action( 'wp_enqueue_scripts', 'cleaner_wp_scripts' );
	
	function cleaner_wp_scripts() {
		
		wp_enqueue_script(
			'cleaner-wp',
			plugins_url() . '/cleaner-wp/js/cleaner-wp.js',
			array( 'jquery' )
		);
		
		wp_register_style( 'cleaner-wp', plugins_url() . '/cleaner-wp/css/cleaner-wp.css' );	
		wp_enqueue_style( 'cleaner-wp' );	
		
		/* Gallery shortcode */
		if  ( ! current_theme_supports( 'cleaner-gallery' ) ) {
			wp_register_style( 'cleaner-wp-gallery', plugins_url() . '/cleaner-wp/css/cleaner-wp-gallery.css' );	
			wp_enqueue_style( 'cleaner-wp-gallery' );
		}		
			
	}
    
/**
 * Performance
 */
 
// Call the google CDN version of jQuery for the frontend
// Make sure you use this with wp_enqueue_script('jquery'); in your header
function wpfme_jquery_enqueue() {
	wp_deregister_script('jquery');
	wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js", false, null);
	wp_enqueue_script('jquery');
}
if (!is_admin()) add_action("wp_enqueue_scripts", "wpfme_jquery_enqueue", 11);


// Call Googles HTML5 Shim, but only for users on old versions of IE
function wpfme_IEhtml5_shim () {
	global $is_IE;
	if ($is_IE)
	echo '<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
}
add_action('wp_head', 'wpfme_IEhtml5_shim');

/**
 * Security
 */

// Obscure login screen error messages
function wpfme_login_obscure(){ return '<strong>Sorry</strong>: Think you have gone wrong somwhere!';}
add_filter( 'login_errors', 'wpfme_login_obscure' );

add_filter( 'use_default_gallery_style', '__return_false' );

// Disable the theme / plugin text editor in Admin
define('DISALLOW_FILE_EDIT', true);

/*	Cleaning-up header
	If you don’t add a PRIORITY then you try to remove the generator before WP has added it, especially in a child theme.
	You haven’t removed the generator from the RSS feed so your efforts are totally in vain. 
	*/
add_action('init', 'removeHeadLinks'); 	
function removeHeadLinks() {   	
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3);
	remove_action( 'wp_head', 'index_rel_link');
	remove_action( 'wp_head', 'parent_post_rel_link', 10, 0);
	remove_action( 'wp_head', 'start_post_rel_link', 10, 0);
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0);
	remove_action( 'wp_head', 'rsd_link');
	remove_action( 'wp_head', 'wlwmanifest_link');
	// Remove the version number of WP
	// Warning - this info is also available in the readme.html file in your root directory - delete this file!	
	remove_action( 'wp_head', 'wp_generator',99);
	/* the only way I manage to remove WP version number from head */
	add_filter( 'the_generator', '__return_null' );
	add_filter( 'hybrid_meta_template', '__return_null' );
}	   
/* ====== END Cleaning up  ====== */
?>
