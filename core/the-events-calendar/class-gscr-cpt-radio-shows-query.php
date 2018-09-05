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
		
		add_filter( 'get_terms', array( $this, 'get_terms' ), 10, 4 );
		
		add_action( 'tribe_events_community_form_before_template', array( $this, 'prevent_community_radio_shows' ) );
		
		add_filter( 'tribe_event_label_singular', array( $this, 'tribe_event_label_singular' ) );
		
		add_filter( 'tribe_event_label_singular_lowercase', array( $this, 'tribe_event_label_singular_lowercase' ) );
		
		add_filter( 'tribe_event_label_plural', array( $this, 'tribe_event_label_plural' ) );
		
		add_filter( 'tribe_event_label_plural_lowercase', array( $this, 'tribe_event_label_plural_lowercase' ) );
		
		add_filter( 'template_include', array( $this, 'start_buffer' ), 99 );
		
		add_filter( 'shutdown', array( $this, 'remove_all_events_link' ), 0 );
		
		add_filter( 'post_type_labels_tribe_events', array( $this, 'post_type_labels_tribe_events' ) );
		
		add_action( 'wp_ajax_tribe_dropdown', array( $this, 'hijack_route' ), 1 );
		add_action( 'wp_ajax_nopriv_tribe_dropdown', array( $this, 'hijack_route' ), 1 );
		
		add_action( 'pre_get_posts', array( $this, 'radio_show_search' ) );
		
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
		
		if ( ( ! is_admin() && is_archive() && strpos( $_SERVER['REQUEST_URI'], 'radio-show' ) === false && strpos( $_SERVER['REQUEST_URI'], 'on-air-personality' ) === false ) || 
			( Tribe__Main::instance()->doing_ajax() && $query->query['tribe_events_cat'] !== 'radio-show' ) ) {
			
			if ( isset( $query->query_vars['eventDisplay'] ) && 
				$query->query_vars['eventDisplay'] !== 'single-event' &&
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
	 * Filter terms output. This is used to prevent Radio Shows as a selection for Community Events
	 * 
	 * @param		array $terms      Array of found terms.
	 * @param		array $taxonomies An array of taxonomies.
	 * @param		array $args       An array of get_terms() arguments.
	 * @param		array $term_query The WP_Term_Query object.
	 *                                             
	 * @access		public
	 * @since		1.0.0
	 * @return		array Terms
	 */
	public function get_terms( $terms, $taxonomies, $args, $term_query ) {
		
		global $allow_radio_shows;
		
		if ( $allow_radio_shows ) return $terms;
		
		$community_base = tribe( 'community.main' )->getOption( 'communityRewriteSlug', 'community', true );
		
		if ( ( ! is_admin() || Tribe__Main::instance()->doing_ajax() ) && 
		   in_array( 'tribe_events_cat', $taxonomies ) ) {

			foreach ( $terms as $index => $term_id ) {

				$term = get_term( $term_id );

				if ( $term->slug == 'radio-show' ) {

					unset( $terms[ $index ] );

				}

			}

			$terms = array_values( $terms );

		}
		
		return $terms;
		
	}
	
	public function prevent_community_radio_shows( $event_id ) {
		
		global $allow_radio_shows;
		$allow_radio_shows = false;
		
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
		
		global $wp_query;
		
		// I'm guessing we run out of memory in this case?
		if ( isset( $wp_query->query_vars['eventDisplay'] ) && 
		   $wp_query->query_vars['eventDisplay'] == 'all' ) {
			return $template;
		}

		ob_start();

		return $template;

	}
	
	/**
	 * Remove "All Events" Link from Event Single. We're doing this on our own via our Breadcrumbs in the Theme. 
	 * This prevents us from needing to maintain a Template File
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		string HTML Content
	 */
	public function remove_all_events_link() {
		
		if ( get_post_type() !== 'tribe_events' ) return '';

		$content = ob_get_clean();
		$content = preg_replace( '/<p(?:.*)class="tribe-events-back(?:.*)\n(?:.*)*\n(?:.*)<\/p>/im', '', $content );
		
		echo $content;

	}
	
	/**
	 * Relabel the Events Post Type
	 * 
	 * @param		object $labels Post Type Labels casted to an Object for some reason
	 *                   
	 * @access		public
	 * @since 		1.0.0
	 * @return		object Post Type Labels
	 */
	public function post_type_labels_tribe_events( $labels ) {

		$labels->name = _x( 'Schedule', 'gscr-cpt-radio-shows' );
		$labels->all_items = _x( 'All Schedule Items', 'gscr-cpt-radio-shows' );
		$labels->singular_name = _x( 'Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->add_new = _x( 'Add Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->add_new_item = _x( 'Add Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->edit_item = _x( 'Edit Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->new_item = _x( 'New Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->view_item = _x( 'View Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->search_items = _x( 'Search Schedule Items', 'gscr-cpt-radio-shows' );
		$labels->not_found = _x( 'No Schedule Items found', 'gscr-cpt-radio-shows' );
		$labels->not_found_in_trash = _x( 'No Schedule Items found in trash', 'gscr-cpt-radio-shows' );
		$labels->parent_item_colon = _x( 'Parent Schedule Item:', 'gscr-cpt-radio-shows' );
		$labels->menu_name = _x( 'Schedule', 'gscr-cpt-radio-shows' );
		$labels->name_admin_bar = _x( 'Schedule Item', 'gscr-cpt-radio-shows' );
		$labels->archives = _x( 'Schedule Archives', 'gscr-cpt-radio-shows' );

		return $labels;

	}
	
	/**
	 * Set our Global to False to bail whenever a Select2 Dropdown is being generated by The Events Calendar
	 * 
	 * @access		public
	 * @since		1.0.1
	 * @return		void
	 */
	public function hijack_route() {
		
		global $allow_radio_shows;
		
		$allow_radio_shows = false;
		
	}
	
	/**
	 * Restrict and sort our Radio Show Searches to being only for Radio Shows in the past and showing most recent First
	 * 
	 * @param		object $query WP_Query Object
	 *                                
	 * @access		public
	 * @since		1.0.4
	 * @return		void
	 */
	public function radio_show_search( $query ) {
		
		if ( ! is_search() ) return;
		
		if ( ! $query->get( 'gscr_radio_show_search' ) ) return;
		
		$query->set( 's', urldecode( $query->get( 's' ) ) );
		
		$query->set( 'sentence', true );
		
		$query->set( 'post_type', 'tribe_events' );
		
		$query->set( 'meta_key', '_EventStartDate' );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'order', 'DESC' );
		
		$query->set( 'tax_query', array(
			'relationship' => 'AND',
			array(
				'taxonomy' => 'tribe_events_cat',
				'field' => 'slug',
				'terms' => array( 'radio-show' ),
				'operator' => 'IN'
			),
		) );
		
		$query->set( 'meta_query', array(
			'relation'    => 'AND',
			array(
				'key' => '_EventEndDate',
				'value' => current_time( 'Y-m-d H:i:s' ),
				'type' => 'DATETIME',
				'compare' => '<',
			),
			array(
				'key' => '_EventHideFromUpcoming',
				'compare' => 'NOT EXISTS',
			),
		) );
		
	}
	
}

$instance = new GSCR_Radio_Shows_Query();