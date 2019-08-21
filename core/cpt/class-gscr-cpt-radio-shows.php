<?php
/**
 * Class CPT_GSCR_On_Air Personalities
 *
 * Creates the post type.
 *
 * @since {{VERSION}}
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT_GSCR_Radio_Shows extends RBM_CPT {

	public $post_type = 'radio-show';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'controls-volumeon';
	public $post_args = array(
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'has_archive' => false,
		'rewrite' => array(
			'slug' => 'radio-show',
			'with_front' => false,
			'feeds' => false,
			'pages' => true
		),
		'menu_position' => 11,
		//'capability_type' => 'radio-show',
	);

	/**
	 * CPT_GSCR_Radio_Shows constructor.
	 *
	 * @since {{VERSION}}
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'Radio Show', 'gscr-cpt-radio-shows' );
		$this->label_plural = __( 'Radio Shows', 'gscr-cpt-radio-shows' );

		$this->labels = array(
			'menu_name' => __( 'Radio Shows', 'gscr-cpt-radio-shows' ),
			'all_items' => __( 'All Radio Shows', 'gscr-cpt-radio-shows' ),
		);

		parent::__construct();
		
		add_filter( "manage_{$this->post_type}_posts_columns", array( $this, 'admin_column_add' ) );
		
		add_action( "manage_{$this->post_type}_posts_custom_column", array( $this, 'admin_column_display' ), 10, 2 );

		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_filter( "post_type_labels_$this->post_type", array( $this, 'change_featured_image_banner_labels' ) );

		add_action( 'init', array( $this, 'register_post_status' ) );

		add_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

		add_action( 'before_delete_post', array( $this, 'before_delete_post' ) );
		
	}
	
	/**
	 * Adds an Admin Column
	 * 
	 * @param		array $columns Array of Admin Columns
	 *                                       
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['on-air-personality'] = __( 'On Air Personality/Personalities', 'gscr-cpt-radio-shows' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * 
	 * @param		string  $column  Admin Column ID
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		{{VERSION}}
	 * @return		void
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'on-air-personality' :
				
				$connected_posts = rbm_cpts_get_p2p_children( 'on-air-personality', $post_id );
				
				if ( ! is_array( $connected_posts ) ) $connected_posts = array( $connected_posts );
				
				echo '<ul style="margin-top: 0; list-style-type: disc; padding-left: 1.25em;">';
				foreach ( $connected_posts as $connected ) : if ( empty( $connected ) ) continue; ?>

					<li>
						<?php edit_post_link( get_the_title( $connected ), '', '', $connected ); ?>
					</li>
					
				<?php endforeach;
				echo '</ul>';
				
				break;
			case 'default' :
				echo rbm_field( $column, $post_id );
				break;
				
		}
		
	}

	public function add_meta_boxes() {

		add_meta_box(
			'radio-show-occurrences',
			__( 'Radio Show Occurrences', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_metabox_content' ),
			$this->post_type,
			'normal'
		);
		
		add_meta_box(
			'radio-show-side-meta',
			__( 'Radio Show Meta', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_side_metabox_content' ),
			$this->post_type,
			'side'
		);

		add_meta_box(
			'radio-show-background-image',
			__( 'Background Image', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_background_image_metabox_content' ),
			$this->post_type,
			'side',
			'low'
		);

		add_meta_box(
			'radio-show-headshot-image',
			__( 'Headshot Image', 'gscr-cpt-radio-shows' ),
			array( $this, 'radio_show_headshot_image_metabox_content' ),
			$this->post_type,
			'side',
			'low'
		);

	}

	public function radio_show_metabox_content() {

		rbm_cpts_do_field_repeater( array(
			'name' => 'radio_show_times',
			'group' => 'radio_show_meta',
			'fields' => array(
				'broadcast_type' => array(
					'type' => 'radio',
					'args' => array(
						'label' => '<strong>' . __( 'Broadcast Type', 'gscr-cpt-radio-shows' ) . '</strong>',
						'options' => array(
							'live' => __( 'Live', 'gscr-cpt-radio-shows' ),
							'encore' => __( 'Encore', 'gscr-cpt-radio-shows' ),
							'none' => __( 'Neither', 'gscr-cpt-radio-shows' ),
						),
					),
				),
				'day_of_the_week' => array(
					'type' => 'select',
					'args' => array(
						'label' => '<strong>' . __( 'Day of the Week', 'gscr-cpt-radio-shows' ) . '</strong>',
						'options' => gscr_get_weekdays(),
						'select2_disable' => true,
					),
				),
				'start_time' => array(
					'type' => 'timepicker',
					'args' => array(
						'label' => '<strong>' . __( 'Start Time', 'gscr-cpt-radio-shows' ) . '</strong>',
					),
				),
				'end_time' => array(
					'type' => 'timepicker',
					'args' => array(
						'label' => '<strong>' . __( 'End Time', 'gscr-cpt-radio-shows' ) . '</strong>',
					),
				),
				'post_id' => array(
					'type' => 'hidden',
				)
			),
		) );

		rbm_cpts_do_field_hidden( array(
			'name' => 'radio_show_occurrences_to_delete',
		) );

		rbm_cpts_init_field_group( 'radio_show_meta' );

	}

	public function radio_show_side_metabox_content() {

		rbm_cpts_do_field_checkbox( array(
			'name' => 'radio_show_is_local',
			'group' => 'radio_show_side_meta',
			'options' => array(
				'1' => __( 'Local Radio Show?', 'gscr-cpt-radio-shows' ),
			),
		) );

		rbm_cpts_do_field_text( array(
			'name' => 'radio_show_call_in',
			'group' => 'radio_show_side_meta',
			'label' => '<strong>' . __( 'Call-In Number', 'gscr-cpt-radio-shows' ) . '</strong>',
		) );

		rbm_cpts_init_field_group( 'radio_show_side_meta' );

	}

	public function radio_show_background_image_metabox_content() {

		rbm_cpts_do_field_media( array(
			'name' => 'radio_show_background_image',
			'type' => 'image',
			'group' => 'radio_show_background_image',
		) );

		rbm_cpts_init_field_group( 'radio_show_background_image' );

	}

	public function radio_show_headshot_image_metabox_content() {

		rbm_cpts_do_field_media( array(
			'name' => 'radio_show_headshot_image',
			'type' => 'image',
			'group' => 'radio_show_headshot_image',
		) );

		rbm_cpts_init_field_group( 'radio_show_headshot_image' );

	}

	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();
		global $pagenow;
		
		if ( $current_screen->post_type == 'radio-show' && 
			( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {

			wp_enqueue_script( 'gscr-cpt-radio-shows-admin' );

		}

	}

	/**
	 * Change Featured Image Labels Radio Shows
	 * 
	 * @param		array $labels Featured Image Labels
	 *                                      
	 * @since		{{VERSION}}
	 * @return		array Featured Image Labels
	 */
	public function change_featured_image_banner_labels( $labels ) {

		$labels->featured_image = __( 'Logo Image', 'gscr-cpt-radio-shows' );
		$labels->set_featured_image = __( 'Set Logo Image', 'gscr-cpt-radio-shows' );
		$labels->remove_featured_image = __( 'Remove Logo Image', 'gscr-cpt-radio-shows' );
		$labels->use_featured_image = __( 'Use as Logo Image', 'gscr-cpt-radio-shows' );

		return $labels;

	}

	/**
	 * Create/Update/Delete Child Radio Shows which are used for determining our Schedule
	 *
	 * @param   integer  $post_id  WP_Post ID
	 *
	 * @since	{{VERSION}}
	 * @return  void
	 */
	public function create_child_radio_shows( $post_id ) {

		if ( get_post_type( $post_id ) !== 'radio-show' ) 
			return;
	
		// Autosave, do nothing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return;

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) 
			return;
		
		// Check user permissions
		if ( ! current_user_can( 'edit_post', $post_id ) )
			return;
		
		// Return if it's a post revision
		if ( false !== wp_is_post_revision( $post_id ) )
			return;

		// Delete Occurrences that have been "marked for deletion"
		if ( isset( $_POST['rbm_cpts_radio_show_occurrences_to_delete'] ) ) {

			$delete_ids = explode( ',', $_POST['rbm_cpts_radio_show_occurrences_to_delete'] );

			foreach ( $delete_ids as $id ) {

				// Make sure a default ID didn't make it through somehow
				if ( (int) $id == 0 ) continue;

				wp_delete_post( (int) $id, true );

			}

		}

		if ( isset( $_POST['rbm_cpts_radio_show_times'] ) ) {

			// Prevent accidentally causing an infinite loop
			remove_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

			foreach ( $_POST['rbm_cpts_radio_show_times'] as &$occurrence ) {

				// Allow creating a new one if needed
				$occurrence_id = ( $occurrence['post_id'] ) ? $occurrence['post_id'] : 0;

				$created_id = wp_insert_post( array(
					'ID' => $occurrence_id,
					'post_type' => 'radio-show',
					'post_title' => $_POST['post_title'],
					'post_content' => $_POST['post_content'],
					'post_parent' => $post_id,
					'post_status' => 'radioshow-occurrence', // Use custom Post Status to prevent it from being accessible on the frontend outside of our schedule-building queries
				), true );

				if ( is_wp_error( $created_id ) ) {
					$errors = implode( ';', $created_id->get_error_messages() );
					error_log( $errors );
					continue;
				}

				// Save here in the Parent
				$occurrence['post_id'] = $created_id;

				foreach ( $occurrence as $meta_key => $meta_value ) {

					// No point in storing this
					if ( $meta_key == 'post_id' ) continue;

					// Assign to the Child so we can use this information for sorting/querying outside of a Serialized Repeater
					update_post_meta( $created_id, "rbm_cpts_$meta_key", $meta_value );

				}

			}

			add_action( 'save_post', array( $this, 'create_child_radio_shows' ) );

			// Update with our changes to include Post IDs
			update_post_meta( $post_id, 'rbm_cpts_radio_show_times', $_POST['rbm_cpts_radio_show_times'] );

		}

	}

	public function register_post_status() {

		register_post_status( 'radioshow-occurrence', array(
			'label' => __( 'Radio Show Occurrence (Hidden)', 'gscr-cpt-radio-shows' ),
			'public' => false, // We're going to show all data on a main, Single template for the parent
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false,
			'post_type' => 'radio-show',
			'internal' => true,
		) );

	}

	public function before_delete_post( $post_id ) {

		// Watch only Radio Shows
		if ( get_post_type( $post_id ) !== 'radio-show' ) return;
		
		$query = new WP_Query( array(
			'post_type' => 'radio-show',
			'post_status' => 'radioshow-occurrence',
			'post_parent' => $post_id,
			'posts_per_page' => -1,
			'fields' => 'ids',
		) );

		if ( ! $query->have_posts() ) return;

		foreach ( $query->posts as $delete_id ) {
			wp_delete_post( $delete_id, true );
		}

	}
	
}