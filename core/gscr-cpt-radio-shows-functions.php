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

/**
 * Returns a localized Array of Weekday names with numeric Indices
 * The "Week Starts On" day in wp_options does not matter here
 * 
 * @since		1.0.0
 * @return		array Localized Weekday names
 */
function gscr_get_weekdays() {
	
	global $wp_locale;

	$options = array();

	foreach ( $wp_locale->weekday as $index => $weekday ) {
		$options[ $index ] = $weekday;
	}
	
	return $options;

}