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
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
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