<?php
/**
 * Provides helper functions.
 *
 * @since	  1.0.0
 *
 * @package	GSCR_CPT_Radio_Shows
 * @subpackage GSCR_CPT_Radio_Shows/core
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Returns the main plugin object
 *
 * @since		1.0.0
 *
 * @return		GSCR_CPT_Radio_Shows
 */
function GSCRCPTRADIOSHOWS() {
	return GSCR_CPT_Radio_Shows::instance();
}