<?php
/**
 * Class GSCR_Radio_Shows_Rewrite_Rules
 *
 * Causes our Permastructs to exist, allowing `/radio-show/` to effectively work as an alias for `/event/`
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class GSCR_Radio_Shows_Rewrite_Rules {

	/**
	 * GSCR_Radio_Shows_Rewrite_Rules constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		
	}
	
	/**
	 * Allow Radio Shows to be viewed using an alternate Permalink Structure
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function add_rewrite_rule() {

		// Get Single (Not Recurring)
		add_rewrite_rule( 'radio-show/([^/]+)/?$', 'index.php?post_type=tribe_events&tribe_events_cat=radio-show&name=$matches[1]', 'top' );

		// Recurring (All and Single)
		add_rewrite_rule( 'radio-show/([^/]*)/([^/]*)/?$', 'index.php?post_type=tribe_events&tribe_events_cat=radio-show&name=$matches[1]&tribe_events=$matches[1]&eventDate=$matches[2]&eventDisplay=$matches[2]', 'top' );
		
	}
	
}

$instance = new GSCR_Radio_Shows_Rewrite_Rules();