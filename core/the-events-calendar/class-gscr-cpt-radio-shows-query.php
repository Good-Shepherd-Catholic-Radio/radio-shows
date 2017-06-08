<?php
/**
 * Class GSCR_Radio_Shows_Query
 *
 * Remove Radio Show results from the main Events Calendar
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class GSCR_Radio_Shows_Query {

	/**
	 * GSCR_Radio_Shows_Query constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		add_action( 'init', array( $this, 'create_term' ), 11 );
		
		add_action( 'tribe_events_pre_get_posts', array( $this, 'remove_radio_shows' ) );
		
		add_filter( 'term_links-tribe_events_cat', array( $this, 'get_the_term_list' ) );
		
	}
	
	public function create_term() {

		if ( ! term_exists( 'radio-show', 'tribe_events_cat' ) ) {

			$test = wp_insert_term(
				__( 'Radio Show', 'gscr-cpt-radio-shows' ),
				'tribe_events_cat'
			);

		}

	}
	
	/**
	 * Remove all Radio Shows from the (regular) Events Calendar
	 * We can still get to them our own way
	 * 
	 * @param		object $query WP_Query
	 *                       
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function remove_radio_shows( $query ) {
		
		if ( is_archive() && ! is_admin() ) {
			
			if ( $query->query_vars['eventDisplay'] !== 'single-event' &&
				$query->query_vars['eventDisplay'] !== 'all' ) {
			
				$tax_query = $query->get( 'tax_query' );

				$exclude = array(
					'taxonomy' => 'tribe_events_cat',
					'field' => 'slug',
					'terms' => array( 'radio-show' ),
					'operator' => 'NOT IN'
				);

				if ( empty( $tax_query ) ) {

					$tax_query = array(
						'relation' => 'AND',
						$exclude
					);

				}
				else {

					$tax_query[] = $exclude;

				}

				$query->set( 'tax_query', $tax_query );
				
			}
			
		}
		
	}
	
	/**
	 * Excludes Radio Shows from any front-end list of Terms
	 * 
	 * @param		array $links Array of HTML Links
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		array Modified Array
	 */
	public function get_the_term_list( $links ) {
		
		// WordPress loves absolute URLs
		$matches = preg_grep( '/\/radio-show\//i', $links );
		
		if ( empty( $matches ) ) return $links;
		
		// This gives us access to the Absolute URL to search for the index
		foreach ( $matches as $match ) {
			
			// We need the Index of the $links array, not the $matches array
			$index = array_search( $match, $links );
			
			unset( $links[ $index ] );
			
		}
		
		return $links;
		
	}
	
}

$instance = new GSCR_Radio_Shows_Query();