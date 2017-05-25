<?php
/**
 * Class CPT_GSCR_Radio_Shows
 *
 * Creates the post type.
 *
 * @since 1.0.0
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

		add_filter( 'get_sample_permalink_html', array( $this, 'alter_permalink_html' ), 10, 5 );

		add_filter( 'the_permalink', array( $this, 'the_permalink' ) );

		add_filter( 'post_type_link', array( $this, 'get_permalink' ), 10, 4 );

		add_filter( 'template_include', array( $this, 'redirect_to_original_radio_show' ) );

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
				'description' => _x( 'This Encore Radio Show will instead redirect to the Original Radio Show when clicked on.', 'Radio Show Original Description', 'gscr-cpt-radio-shows' ),
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

	/**
	 * Show the the "Encored" Radio Show as the Permalink Sample if this Radio Show is an Encore
	 * 
	 * @param		string  $return    Sample HTML Markup
	 * @param		integer $post_id   Post ID
	 * @param		string  $new_title New Sample Permalink Title
	 * @param		string  $new_slug  New Sample Permalnk Slug
	 * @param		object  $post      WP Post Object
	 *                   
	 * @access		public
	 * @since		1.0.1
	 * @return		string  Modified HTML Markup
	 */
	public function alter_permalink_html( $return, $post_id, $new_title, $new_slug, $post ) {

		// No sense in a database query if it isn't the correct Post Type
		if ( $post->post_type == $this->post_type ) {

			if ( rbm_get_field( 'radio_show_encore', $post_id ) &&
				$radio_show_id = rbm_get_field( 'radio_show_original', $post_id ) ) {

				$url = get_permalink( $radio_show_id );

				$return = preg_replace( '/<a.*<\/a>/', '<a href="' . $url . '">' . $url . '</a>', $return );
				$return = str_replace( '<span id="edit-slug-buttons"><button type="button" class="edit-slug button button-small hide-if-no-js" aria-label="Edit permalink">Edit</button></span>', '', $return );

			}

		}

		return $return;

	}

	/**
	 * Replace the_permalink() calls on the Frontend with the original Radio Show's URL
	 * 
	 * @param		string $url The Post URL
	 *                
	 * @access		public
	 * @since		1.0.1
	 * @return		string Modified URL
	 */
	public function the_permalink( $url ) {

		global $post;

		// No sense in a database query if it isn't the correct Post Type
		if ( $post->post_typ == $this->post_type ) {

			if ( rbm_get_field( 'radio_show_encore', $post->ID ) &&
				$radio_show_id = rbm_get_field( 'radio_show_original', $post->ID ) ) {

				$url = get_permalink( $radio_show_id );

			}

		}

		return $url;

	}

	/**
	 * Replace get_peramlink() calls on the Frontend with the original Radio Show's URL
	 * 
	 * @param		string  $url       The Post URL
	 * @param		object  $post      WP Post Object
	 * @param		boolean $leavename Whether to leave the Post Name
	 * @param		boolean $sample    Is it a sample permalink?
	 *     
	 * @access		public
	 * @since		1.0.1
	 * @return		string  Modified URL
	 */
	public function get_permalink( $url, $post, $leavename = false, $sample = false ) {

		global $post;

		// No sense in a database query if it isn't the correct Post Type
		if ( $post->post_type == $this->post_type ) {

			if ( rbm_get_field( 'radio_show_encore', $post->ID ) &&
				$radio_show_id = rbm_get_field( 'radio_show_original', $post->ID ) ) {

				// Prevent infinite recursion
				$url = $this->get_post_permalink( $radio_show_id );

			}

		}

		return $url;

	}

	/**
	 * Copy of get_post_permalink() to prevent infinite recursion on our post_type_link Filter
	 * It is entirely the same as what's in Core (As of WP 4.7.5) outside of having the post_type_link Filter at the end
	 * 
	 * @param		integer $id WP_Post ID
	 * @param		boolean Whether to keep post name. Default false
	 * @param		boolean Is it a sample permalink. Default false
	 *                             
	 * @access		private
	 * @since		1.0.0
	 * @return		string  Permalink
	 */
	private function get_post_permalink( $id, $leavename = false, $sample = false ) {
		
		global $wp_rewrite;

		$post = get_post( $id );

		if ( is_wp_error( $post ) )
			return $post;

		$post_link = $wp_rewrite->get_extra_permastruct($post->post_type);

		$slug = $post->post_name;

		$draft_or_pending = get_post_status( $id ) && in_array( get_post_status( $id ), array( 'draft', 'pending', 'auto-draft', 'future' ) );

		$post_type = get_post_type_object($post->post_type);

		if ( $post_type->hierarchical ) {
			$slug = get_page_uri( $id );
		}

		if ( !empty($post_link) && ( !$draft_or_pending || $sample ) ) {
			if ( ! $leavename ) {
				$post_link = str_replace("%$post->post_type%", $slug, $post_link);
			}
			$post_link = home_url( user_trailingslashit($post_link) );
		} else {
			if ( $post_type->query_var && ( isset($post->post_status) && !$draft_or_pending ) )
				$post_link = add_query_arg($post_type->query_var, $slug, '');
			else
				$post_link = add_query_arg(array('post_type' => $post->post_type, 'p' => $post->ID), '');
			$post_link = home_url($post_link);
		}
		
		return $post_link;

	}

	/**
	 * Force a redirect to the original Radio Show's URL if one exists
	 * 
	 * @param       string $template Path to Template File
	 *    
	 * @access		public
	 * @since       1.0.1
	 * @return      string Modified Template File Path
	 */
	public function redirect_to_original_radio_show( $template ) {

		global $wp_query;
		global $post;

		$post_type = get_post_type();

		if ( is_single() && ( $post_type == $this->post_type ) ) {

			if ( rbm_get_field( 'radio_show_encore', $post->ID ) &&
				$radio_show_id = rbm_get_field( 'radio_show_original', $post->ID ) ) {

				$url = get_permalink( $radio_show_id );

				header( "Location: $url", true, 301 );

			}

		}

		return $template;

	}

}