<?php
/**
 * Class CPT_GSCR_Radio_Shows
 *
 * Creates the post type.
 *
 * @since 1.0.0
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
		'has_archive' => true,
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
	 * @since 1.0.0
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
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
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
			sprintf( _x( '%s Meta', 'Metabox Title', 'gscr-cpt-radio-shows' ), $this->label_singular ),
			array( $this, 'metabox_content' ),
			$this->post_type,
			'normal'
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
		
		?>

		<p class="description">
			blah
		</p>

		<?php
		
		rbm_do_field_timepicker(
			'radio_show_time_start',
			_x( 'Radio Show Start Time', 'Radio Show Start Time Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
			)
		);
		
		rbm_do_field_timepicker(
			'radio_show_time_end',
			_x( 'Radio Show End Time', 'Radio Show End Time Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
			)
		);
		
		global $wp_locale;
		
		$options = array();
		
		foreach ( $wp_locale->weekday as $weekday ) {
			$options[ $weekday ] = $weekday;
		}
		
		rbm_do_field_radio(
			'radio_show_date',
			_x( 'Radio Show Date', 'Radio Show Date Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
				'options' => $options,
			)
		);
		
		rbm_do_field_checkbox(
			'radio_show_encore',
			_x( 'Radio Show Encore?', 'Radio Show Encore Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
				'wrapper_class' => 'radio-show-encore',
			)
		);
		
		$radio_shows = new WP_Query( array(
			'post_type' => 'radio-show',
			'posts_per_page' => -1,
		) );
		
		$radio_shows = wp_list_pluck( $radio_shows->posts, 'post_title', 'ID' );
		
		// Nice try
		if ( isset( $_GET['post'] ) ) unset( $radio_shows[ $_GET['post'] ] );
		
		rbm_do_field_select(
			'radio_show_original',
			_x( 'Which Radio Show is this an Encore of?', 'Radio Show Original Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
				'options' => array( '' => _x( 'Select a Radio Show', 'Select a Radio Show Text', 'gscr-cpt-radio-shows' ) ) + $radio_shows,
				'wrapper_class' => 'radio-show-original' . ( ( ! rbm_get_field( 'radio_show_original' ) ) ? ' hidden' : '' ),
			)
		);
		
		rbm_do_field_checkbox(
			'radio_show_live',
			_x( 'Live Radio Show?', 'Live Radio Show Label', 'gscr-cpt-radio-shows' ),
			false,
			array(
				'wrapper_class' => 'radio-show-live',
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
	
	public function admin_enqueue_scripts() {
		
		$current_screen = get_current_screen();
		global $pagenow;
		
		if ( $current_screen->post_type == $this->post_type && 
			( in_array( $pagenow, array( 'post-new.php', 'post.php' ) ) ) ) {
			
			wp_enqueue_script( 'gscr-cpt-radio-shows-admin' );
			
		}
		
	}
	
	/**
	 * Adds an Admin Column
	 * 
	 * @param		array $columns Array of Admin Columns
	 *                                       
	 * @access		public
	 * @since		1.0.0
	 * @return		array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['on-air-personality'] = _x( 'On Air Personalities', 'On Air Personalities Column Label', 'gscr-cpt-radio-shows' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * 
	 * @param		string  $column  Admin Column ID
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'on-air-personality' :
				
				$connected_posts = rbm_cpts_get_p2p_children( $column, $post_id );
				
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
	
}