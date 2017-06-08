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
		
		add_action( 'tribe_events_pre_get_posts', array( $this, 'remove_radio_shows' ), 999 );
		
		add_filter( 'term_links-tribe_events_cat', array( $this, 'get_the_term_list' ) );
		
		add_filter( 'tribe_event_label_singular', array( $this, 'tribe_event_label_singular' ) );
		
		add_filter( 'tribe_event_label_singular_lowercase', array( $this, 'tribe_event_label_singular_lowercase' ) );
		
		add_filter( 'tribe_event_label_plural', array( $this, 'tribe_event_label_plural' ) );
		
		add_filter( 'tribe_event_label_plural_lowercase', array( $this, 'tribe_event_label_plural_lowercase' ) );
		
		add_filter( 'template_include', array( $this, 'start_buffer' ), 99 );
		
		add_filter( 'shutdown', array( $this, 'remove_all_events_link' ), 0 );
		
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
		
		if ( ( ! is_admin() && is_archive() ) || 
			Tribe__Main::instance()->doing_ajax() ) {
			
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
	
	/**
	 * Replace Singular Label for Radio Shows
	 * 
	 * @param		string $singular_uppercase
	 *
	 * @since		1.0.0
	 * @return		string
	 */
	public function tribe_event_label_singular( $singular_uppercase ) {
		
		$terms = wp_get_post_terms( get_the_ID(), 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $singular_uppercase;
		
		return __( 'Radio Show', 'gscr-cpt-radio-shows' );
		
	}
	
	/**
	 * Replace Singular Label for Radio Shows
	 * 
	 * @param		string $singular_lowercase
	 *
	 * @since		1.0.0
	 * @return		string
	 */
	public function tribe_event_label_singular_lowercase( $singular_lowercase ) {
		
		$terms = wp_get_post_terms( get_the_ID(), 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $singular_lowercase;
		
		return __( 'radio show', 'gscr-cpt-radio-shows' );
		
	}
	
	/**
	 * Replace Plural Label for Radio Shows
	 * 
	 * @param		string $plural_uppercase
	 *
	 * @since		1.0.0
	 * @return		string
	 */
	public function tribe_event_label_plural( $plural_uppercase ) {
		
		$terms = wp_get_post_terms( get_the_ID(), 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $plural_uppercase;
		
		return __( 'Radio Shows', 'gscr-cpt-radio-shows' );
		
	}
	
	/**
	 * Replace Singular Label for Radio Shows
	 * 
	 * @param		string $plural_lowercase
	 *
	 * @since		1.0.0
	 * @return		string
	 */
	public function tribe_event_label_plural_lowercase( $plural_lowercase ) {
		
		$terms = wp_get_post_terms( get_the_ID(), 'tribe_events_cat' );
		
		// Flatten down the returned Array of Objects into just an Associative Array
		$terms = wp_list_pluck( $terms, 'slug', 'term_id' );
		
		if ( ! in_array( 'radio-show', $terms ) ) return $plural_lowercase;
		
		return __( 'radio shows', 'gscr-cpt-radio-shows' );
		
	}
	
	/**
	 * Creates an Object Buffer for us to alter the rendered HTML later
	 * 
	 * @param		string Template File
	 * 
	 * @since		1.0.0
	 * @return		string Template File
	 */
	public function start_buffer( $template ) {

		ob_start();

		return $template;

	}
	
	/**
	 * Forcibly injects Google Tag Manager code after the opening <body> tag without needing to edit header.php in the Parent Theme
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		string HTML Content
	 */
	public function remove_all_events_link() {
		
		if ( get_post_type() !== 'tribe_events' ) return $content;

		$content = ob_get_clean();
		$content = preg_replace( '/<p(?:.*)class="tribe-events-back(?:.*)\n(?:.*)*\n(?:.*)<\/p>/im', '', $content );
		
		echo $content;

	}
	
}

$instance = new GSCR_Radio_Shows_Query();