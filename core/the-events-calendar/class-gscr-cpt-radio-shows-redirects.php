<?php
/**
 * Class GSCR_Radio_Shows_Redirects
 *
 * Causes our Permastructs to exist, allowing `/radio-show/` to effectively work as an alias for `/event/`
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class GSCR_Radio_Shows_Redirects {

	/**
	 * GSCR_Radio_Shows_Redirects constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		add_filter( 'get_sample_permalink_html', array( $this, 'alter_permalink_html' ), 10, 5 );
		add_filter( 'the_permalink', array( $this, 'the_permalink' ) );	
		add_filter( 'post_type_link', array( $this, 'get_permalink' ), 10, 4 );
		
		// Ensures we land at the permastruct, in the off-chance that it is added as `/event/` somewhere
		add_filter( 'template_include', array( $this, 'redirect_to_permastruct' ) );
		
	}
	
	/**
	 * Show our custom Permastruct in the URL Preview on the Edit Screen
	 * 
	 * @param		string  $return    Sample HTML Markup
	 * @param		integer $post_id   Post ID
	 * @param		string  $new_title New Sample Permalink Title
	 * @param		string  $new_slug  New Sample Permalnk Slug
	 * @param		object  $post      WP Post Object
	 *                   
	 * @access		public
	 * @since		1.0.0
	 * @return		string  Modified HTML Markup
	 */
	public function alter_permalink_html( $return, $post_id, $new_title, $new_slug, $post ) {
		
		if ( $post->post_type !== 'tribe_events' ) return $return;
		
		$terms = wp_get_post_terms( $post_id, 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $return;
		
		$return = str_replace( '/event/', '/radio-show/', $return );
		
		return $return;
		
	}
	
	/**
	 * Replace the_permalink() calls on the Frontend with the new Permastruct
	 * 
	 * @param		string $url The Post URL
	 *                
	 * @access		public
	 * @since		1.0.0
	 * @return		string Modified URL
	 */
	public function the_permalink( $url ) {
		
		global $post;
		
		if ( $post->post_type !== 'tribe_events' ) return $url;
		
		$terms = wp_get_post_terms( $post->ID, 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $url;
		
		$url = str_replace( '/event/', '/radio-show/', $url );
		
		return $url;
		
	}
	
	/**
	 * Replace get_peramlink() calls on the Frontend with the new Permastruct
	 * 
	 * @param		string  $url       The Post URL
	 * @param		object  $post      WP Post Object
	 * @param		boolean $leavename Whether to leave the Post Name
	 * @param		boolean $sample    Is it a sample permalink?
	 *     
	 * @access		public
	 * @since		1.0.0
	 * @return		string  Modified URL
	 */
	public function get_permalink( $url, $post, $leavename = false, $sample = false ) {
		
		if ( $post->post_type !== 'tribe_events' ) return $url;
		
		$terms = wp_get_post_terms( $post->ID, 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $url;
		
		$url = str_replace( '/event/', '/radio-show/', $url );
		
		return $url;
		
	}
	
	/**
	 * Force a redirect to the PDF if one exists
	 * 
	 * @param       string $template Path to Template File
	 *                                                
	 * @since       1.0.0
	 * @return      string Modified Template File Path
	 */
	public function redirect_to_permastruct( $template ) {
		
		global $post;
		
		if ( ! is_single() || $post->post_type !== 'tribe_events' ) return $template;
		
		$terms = wp_get_post_terms( $post->ID, 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $template;
		
		// Ensure we don't accidentally redirect infinitely
		$url = $_SERVER['REQUEST_URI'];
		if ( strpos( $url, '/event/' ) === false ) return $template;
		
		$url = str_replace( '/event/', '/radio-show/', $url );
		
		header( "Location: $url", true, 301 );
		
	}
	
}

$instance = new GSCR_Radio_Shows_Redirects();