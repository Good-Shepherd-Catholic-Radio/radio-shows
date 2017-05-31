<?php
/**
 * Class GSCR_Radio_Shows_Fields
 *
 * Remove Radio Show results from the main Events Calendar
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class GSCR_Radio_Shows_Fields {

	/**
	 * GSCR_Radio_Shows_Fields constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 1 );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
	}
	
	/**
	 * Add Meta Box
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function add_meta_boxes() {
		
		add_meta_box(
			'radio-show-meta',
			_x( 'Radio Show Meta', 'Metabox Title', 'gscr-cpt-radio-shows' ),
			array( $this, 'metabox_content' ),
			'tribe_events',
			'side'
		);
		
	}
	
	/**
	 * Add Meta Field
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function metabox_content() {

		rbm_do_field_checkbox(
			'radio_show_live',
			_x( 'Live Radio Show?', 'Live Radio Show Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
			)
		);

		rbm_do_field_checkbox(
			'radio_show_local',
			_x( 'Local Radio Show?', 'Local Radio Show Label', 'gscr-cpt-radio-shows' ),
			false,
			array(

			)
		);
		
	}
	
	/**
	 * Enqueue Admin Scripts/CSS
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_enqueue_scripts() {
		
		$current_screen = get_current_screen();
		global $pagenow;
		
		if ( $current_screen->post_type == 'tribe_events' && 
			( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {
			
			wp_enqueue_script( 'gscr-cpt-radio-shows-admin' );
			
		}
	}
		
}

$instance = new GSCR_Radio_Shows_Fields();