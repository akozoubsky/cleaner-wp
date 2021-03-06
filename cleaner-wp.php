<?php
/**
 * Plugin Name: Cleaner WP
 * Plugin URI: https://github.com/akozoubsky/cleaner-wp
 * Description: Clean WordPress Features and Styles.
 * Version: 0.0.4
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
 */

final class Cleaner_WP {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Stores the directory path for this plugin.
	 *
	 * @since  0.0.1
	 * @access private
	 * @var    string
	 */
	private $directory_path;

	/**
	 * Plugin setup.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Set the properties needed by the plugin. */
		add_action( 'plugins_loaded', array( $this, 'setup' ), 1 );

		add_action( 'pre_get_posts', array( $this, 'clwp_pre_get_posts' ), 3 );

		add_filter('the_content', array( $this, 'clwp_remove_img_ptags' ) );

		//add_action( 'admin_head_media_upload_gallery_form', array( $this, 'clwp_mfields_remove_gallery_setting_div' ), 7 );

		add_action('init', array( $this, 'clwp_removeWPHeadTags' ), 12 );

		add_filter( 'login_errors', array( $this, 'clwp_login_obscure' ), 13 );
		
		add_action( 'login_enqueue_scripts', array( $this, 'clwp_login_css' ) );

		add_filter( 'use_default_gallery_style', '__return_false' );
		
	}

	/**
	 * Defines the directory path and URI for the plugin.
	 *
	 * @since  0.0.1
	 * @access public
	 * @return void
	 */
	public function setup() {

		$this->directory_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->directory_uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	/**
	 * Blog query and pagination.
	 *
	 * @since  0.0.3
	 * @access public
	 * @return void
	 */
	function clwp_pre_get_posts( $query ) {

		/* do not include password protected posts for not logged in users */
		if ( $query->is_main_query() &&  !is_user_logged_in() ) {
			$query->set( 'post_password', '' );
		}

		return $query;
	}

	/**
	 * Output
	 */

	// Stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
	// http://blog.iamcreative.me/code-snippets/remove-phantom-paragraph-tags-iframes-images-wordpress/
	public function clwp_remove_img_ptags($content){
		$content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
		return preg_replace('/<p>\s*(<iframe .*>*.<\/iframe>)\s*<\/p>/iU', '\1', $content);
	}

	// Remove all gallery settings from the media upload section */

	function clwp_mfields_remove_gallery_setting_div() {
		print '
		&lt;style type="text/css">
			#gallery-settings *{
			display:none;
			}
		&lt;/style>';
	}

	/**
	 * Security
	 */

	// Obscure login screen error messages
	public function clwp_login_obscure() { return null; }

	function clwp_login_css() {
		print '
		<style type="text/css">
			.login #login_error { display: none; }		
		</style>';
	}

	/**
	 * Cleaning-up header
	 * If you don’t add a PRIORITY then you try to remove the generator before WP has added it, especially in a child theme.
	 * You haven’t removed the generator from the RSS feed so your efforts are totally in vain.
	 */

	public function clwp_removeWPHeadTags() {
		
		//remove_action( 'wp_head', 'feed_links', 2 ); /* This feature adds RSS feed links to HTML <head>. */
		//remove_action( 'wp_head', 'feed_links_extra', 3);
		//remove_action( 'wp_head', 'index_rel_link');
		//remove_action( 'wp_head', 'parent_post_rel_link', 10, 0);
		//remove_action( 'wp_head', 'start_post_rel_link', 10, 0);
		//remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
		//remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0);
		
		remove_action( 'wp_head', 'rsd_link');
		remove_action( 'wp_head', 'wlwmanifest_link');
		
		// Remove the version number of WP
		// Warning - this info is also available in the readme.html file in your root directory - delete this file!
		remove_action( 'wp_head', 'wp_generator',99);
		
		/* the only way I manage to remove WP version number from head */
		add_filter( 'the_generator', '__return_null' );
		
		/* hybrid core framework */
		add_filter( 'hybrid_meta_template', '__return_null' );
	}
	/* ====== END Cleaning up  ====== */

	/**
	 * Returns the instance.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}

}

Cleaner_WP::get_instance();

?>
