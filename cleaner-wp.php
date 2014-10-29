<?php
/**
 * Plugin Name: Cleaner WP
 * Plugin URI: https://github.com/akozoubsky/cleaner-wp
 * Description: Clean WordPress Features and Styles. 
 * Version: 0.0.2
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
			
		/* Set up theme support. */
		add_action( 'after_setup_theme', array( $this, 'theme_support' ), 12 );
					
		add_action( 'pre_get_posts', array( $this, 'search_results_per_page' ), 3 );
		
		add_filter( 'body_class', array( $this, 'clwp_body_class' ), 4 ); 
		
		add_filter('body_class', array( $this, 'has_sidebar' ), 5 );
		
		add_filter('the_content', array( $this, 'remove_img_ptags' ), 6 );
		
		add_action( 'admin_head_media_upload_gallery_form', array( $this, 'mfields_remove_gallery_setting_div' ), 7 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'cleaner_wp_scripts' ), 8 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'jquery_enqueue' ), 9 );
		
		add_action('wp_head', array( $this, 'IEhtml5_shim' ), 10 );
		
		add_action('init', array( $this, 'max_content_width' ), 11 );
		
		add_action('init', array( $this, 'removeHeadLinks' ), 12 ); 
		
		/* add_filter( 'login_errors', array( $this, 'login_obscure' ), 13 ); */
		add_filter( 'login_errors', array( $this, 'login_obscure' ), 13 );
		
		add_filter( 'use_default_gallery_style', '__return_false' );
		
		// Disable the theme / plugin text editor in Admin
		define('DISALLOW_FILE_EDIT', true);
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
	 * Removes ' ' theme support.  This is so that the plugin will take over the 
	 * widgets instead of themes built on Hybrid Core.  Plugin updates can get out quicker to users, 
	 * so the plugin should have priority.
	 *
	 * @since  0.0.2
	 * @access public
	 * @return void
	 */	
	public function theme_support() {
		return;
	}	

	/**
	 * Queries
	 */
	  
	//change amount of posts on the search page - set here to 100
	public function search_results_per_page( $query ) {
		global $wp_the_query;
		if ( ( ! is_admin() ) && ( $query === $wp_the_query ) && ( $query->is_search() ) ) {
		$query->set( 'search_results_per_page', 100 );
		}
		return $query;
	}

	/**
	 * Output
	 */
	 
	/* Add classes to the tag body. Usefull with CSS in yout theme. */	

	public function clwp_body_class( $classes ) {
		
		if ( get_header_image() )
			$classes[] = 'display-header-image'; /* Post or page with header image. */
			
		if ( get_background_image() )
			$classes[] = 'display-background-image'; /* Post or page with background image. */
			
		if ( has_nav_menu( 'primary' ) )
			$classes[] = 'display-primary-menu'; /* Primary Menu is activated. */

		return $classes;
	} 
 
	/* Adds a has_sidebar class to the body if there's a sidebar.  */
	public function has_sidebar($classes) {
		if (is_active_sidebar('sidebar')) {
			// add 'class-name' to the $classes array
			$classes[] = 'has_sidebar';
		}
		// return the $classes array
		return $classes;
	}

	// Stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
	public function remove_img_ptags($content){
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	}


	// Set a maximum width for Oembedded objects
	public function max_content_width() {
		if ( ! isset( $content_width ) )
		$content_width = 1024;
	}

	/**
	 * Output - JQuery + CSS
	 */
	public function cleaner_wp_scripts() {
		
		wp_register_script('cleaner-wp', "{$this->directory_uri}js/cleaner-wp.js", array('jquery'),'0.0.1', true);
		wp_enqueue_script('cleaner-wp');
		
		wp_register_style( 'cleaner-wp', "{$this->directory_uri}css/cleaner-wp.css" );	
		wp_enqueue_style( 'cleaner-wp' );	
		
		/* Gallery shortcode */
		wp_register_style( 'cleaner-wp-gallery', "{$this->directory_uri}css/cleaner-wp-gallery.css");	
		wp_enqueue_style( 'cleaner-wp-gallery' );
	}
    
	/**
	 * Performance
	 */
	 
	// Call the google CDN version of jQuery for the frontend
	// Make sure you use this with wp_enqueue_script('jquery'); in your header
	public function jquery_enqueue() {
		if ( !is_admin() ) {
			wp_deregister_script('jquery');
			wp_register_script('jquery', "http" . ($_SERVER['SERVER_PORT'] == 443 ? "s" : "") . "://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js", false, null);
			wp_enqueue_script('jquery');
		}
	}


	// Call Googles HTML5 Shim, but only for users on old versions of IE
	public function IEhtml5_shim () {
		global $is_IE;
		if ($is_IE)
		echo '<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><![endif]-->';
	}

	/**
	 * Security
	 */

	// Obscure login screen error messages
	public function login_obscure() { return null; }

	/*	Cleaning-up header
		If you don’t add a PRIORITY then you try to remove the generator before WP has added it, especially in a child theme.
		You haven’t removed the generator from the RSS feed so your efforts are totally in vain. 
		*/
	
	public function removeHeadLinks() {   	
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
