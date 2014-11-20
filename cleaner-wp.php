<?php
/**
 * Plugin Name: Cleaner WP
 * Plugin URI: https://github.com/akozoubsky/cleaner-wp
 * Description: Clean WordPress Features and Styles. 
 * Version: 0.0.3
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
					
		add_action( 'pre_get_posts', array( $this, 'clwp_pre_get_posts' ), 3 );
		
		add_filter( 'body_class', array( $this, 'clwp_body_class' ), 4 ); 
		
		add_filter('the_content', array( $this, 'remove_img_ptags' ), 6 );
		
		add_action( 'admin_head_media_upload_gallery_form', array( $this, 'mfields_remove_gallery_setting_div' ), 7 );
		
		add_action( 'wp_enqueue_scripts', array( $this, 'cleaner_wp_scripts' ), 8 );
			
		add_action('init', array( $this, 'removeHeadLinks' ), 12 ); 
		
		/* add_filter( 'login_errors', array( $this, 'login_obscure' ), 13 ); */
		add_filter( 'login_errors', array( $this, 'login_obscure' ), 13 );
		
		add_filter( 'use_default_gallery_style', '__return_false' );
		
		add_filter('xmlrpc_methods', 'clwp_alter_hosting_provider_filters');
		
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
	 
	/* Add classes to the tag body. Usefull with CSS in yout theme. */	

	public function clwp_body_class( $classes ) {
		
		if ( get_header_image() )
			$classes[] = 'display-header-image'; /* Post or page with header image. */
			
		if ( get_background_image() )
			$classes[] = 'display-background-image'; /* Post or page with background image. */
			
		if ( has_nav_menu( 'primary' ) )
			$classes[] = 'display-primary-menu'; /* Primary Menu is activated. */

		if ( is_active_sidebar('sidebar') ) {
			// add 'class-name' to the $classes array
			$classes[] = 'has_sidebar';
		}
		
		return $classes;
	} 
 
	// Stop images getting wrapped up in p tags when they get dumped out with the_content() for easier theme styling
	public function remove_img_ptags($content){
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	}

	/**
	 * Output - JQuery + CSS
	 */
	public function cleaner_wp_scripts() {
		
		wp_enqueue_script('cleaner-wp-js', "{$this->directory_uri}js/cleaner-wp.js", array('jquery'),'0.0.1', true);
		
		wp_enqueue_style( 'cleaner-wp', "{$this->directory_uri}css/cleaner-wp.css" );	
	}  

	/**
	 * Security
	 */
	 
	/**
	 * Begin modifications requested by hosting providers.
	 *
	 * You can safely remove this file to return your installation
	 * to a vanilla state.
	 */

	/**
	 * The following modification was requested by BlueHost, 7/9/2014
	 * due to a high level of abuse and DDOS usage.
	 *
	 * To re-enable xmlrpc pingbacks, you can remove the code below this comment.
	 *
	 * For more info, see here:
	 * http://blog.spiderlabs.com/2014/03/wordpress-xml-rpc-pingback-vulnerability-analysis.html
	 */

	public function clwp_alter_hosting_provider_filters( $methods ) {
		
		if ( isset( $methods['pingback.ping'] ) ) {
			/* Disable Pingback Reqests */
			unset( $methods['pingback.ping'] );
		}

		return $methods;
	}

	// Obscure login screen error messages
	public function login_obscure() { return null; }

	/**
	 * Cleaning-up header
	 * If you don’t add a PRIORITY then you try to remove the generator before WP has added it, especially in a child theme.
	 * You haven’t removed the generator from the RSS feed so your efforts are totally in vain. 
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
