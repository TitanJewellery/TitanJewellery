<?php
/**
 * Plugin Name:       TJ Appt Booker
 * Description:       Titan Jewellery consultation booking system, no-payment security build. The full engine only loads on requests that actually need it; other front-end pages remain unaffected.
 * Version:           1.16.8
 * Author:            Jason Beer of Titan Jewellery
 * Author URI:        https://www.titanjewellery.co.uk/
 * Requires at least: 6.0
 * Requires PHP:      7.4
 *
 * ---------------------------------------------------------------------------
 * HOW THIS WORKS
 * ---------------------------------------------------------------------------
 * The original Code Snippets version (snippet 871, ~707KB) was fetched from
 * the database, parsed, and fully hooked on EVERY request - product pages,
 * blog posts, everything. This bootstrap replaces that with a two-file
 * structure:
 *
 *   tj-appt-booker.php                     <- this file. Tiny. Loads always,
 *                                             decides whether the engine is
 *                                             needed. OPcache-compiled once.
 *   includes/class-appt-booker.php   <- the current booking engine.
 *                                             Only loaded when a trigger fires.
 *
 * The engine loads when ANY of these is true:
 *   1. Admin request (booking admin screens, postbacks, dashboard widget).
 *   2. Cron request (daily summary, retention sweep, queued email events).
 *   3. WP-CLI.
 *   4. AJAX request whose action begins "rgl_booking_" (all booking handlers
 *      share this prefix; one string comparison covers them all).
 *   5. Front-end URL carrying a booking query arg (booking summary,
 *      cancellation, rescheduling, approval or ICS download).
 *   6. A singular front-end page whose content contains one of the booking
 *      shortcodes (checked once on the `wp` hook, including inside
 *      Elementor data if the page is built with Elementor).
 *
 * On every other front-end request the total cost is this file (from
 *  OPcache) plus a handful of string comparisons. No queries, no hooks,
 * no CSS/JS, no jQuery UI Datepicker.
 *
 * NOTE ON TIMING: when the engine loads via trigger 6 (`wp` hook), the
 * `init` hook has already fired, so the engine's init-registered pieces
 * (CPT registration, Elementor content sync and cron scheduling) simply do not run on those page views. That is
 * intentional - none of them are needed to render the booking form.
 * They all still run on admin, cron, AJAX, and URL-trigger requests,
 * which load the engine at plugins_loaded, before init.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TJ_APPT_BOOKER_VERSION', '1.16.8' );
define( 'TJ_APPT_BOOKER_ENGINE', __DIR__ . '/includes/class-appt-booker.php' );
define( 'TJ_APPT_BOOKER_LICENCE', __DIR__ . '/includes/class-appt-booker-licence.php' );

// Licence enforcement must load on every request, even when the full booking
// engine remains dormant on unrelated front-end pages.
//
// If this file is missing the engine treats the site as unlicensed and the
// [appt_booker] shortcode renders nothing at all - no form, no message. That is
// impossible to diagnose from the front end, so say so in the admin the same
// way a missing engine file does.
if ( is_readable( TJ_APPT_BOOKER_LICENCE ) ) {
	require_once TJ_APPT_BOOKER_LICENCE;
} elseif ( is_admin() ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p><strong>TJ Appt Booker:</strong> licence file <code>includes/class-appt-booker-licence.php</code> is missing or unreadable. The plugin is treated as unlicensed, and the public booking form will not be displayed. Re-upload the complete plugin folder.</p></div>';
	} );
}

/**
 * Load the full booking engine exactly once.
 *
 * The engine file contains the current hardened no-payment build. It ends with
 * appt_booker_final_boot(), so simply requiring it registers everything.
 */
function tj_appt_booker_load_engine() {
	static $loaded = false;
	if ( $loaded ) {
		return true;
	}
	if ( ! is_readable( TJ_APPT_BOOKER_ENGINE ) ) {
		// Engine file missing - fail safely and tell the admin.
		if ( is_admin() ) {
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-error"><p><strong>TJ Appt Booker:</strong> engine file <code>includes/class-appt-booker.php</code> is missing or unreadable. The booking system is inactive.</p></div>';
			} );
		}
		return false;
	}
	$loaded = true;
	require_once TJ_APPT_BOOKER_ENGINE;
	return true;
}

/**
 * Does the current request carry a booking-related query arg?
 *
 * Covers the booking summary page, ICS download, customer cancellation
 * and admin approval links (GET confirmation page plus POST action).
 */
function tj_appt_booker_request_has_booking_args() {
	$keys = array(
		'rgl_booking_summary',
		'rgl_booking_ics',
		'rgl_booking_cancel',
		'rgl_booking_reschedule',
		'rgl_booking_approve',
	);
	foreach ( $keys as $key ) {
		if ( isset( $_REQUEST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return true;
		}
	}
	return false;
}

/**
 * Main gate. Runs at plugins_loaded (before init) so that engine init
 * hooks still fire for admin / cron / AJAX / URL-trigger requests.
 */
add_action( 'plugins_loaded', function () {

	// AJAX first: admin-ajax.php reports is_admin() as true, so this
	// branch must come before the general admin check or every front-end
	// AJAX call (add-to-cart etc.) would load the engine.
	if ( wp_doing_ajax() ) {
		$action = isset( $_REQUEST['action'] ) ? (string) wp_unslash( $_REQUEST['action'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( 0 === strpos( $action, 'rgl_booking_' ) ) {
			tj_appt_booker_load_engine();
		}
		return;
	}

	if ( is_admin() || wp_doing_cron() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		tj_appt_booker_load_engine();
		return;
	}

	// Front end: booking URLs (summary, cancel, reschedule, approve and ICS).
	if ( tj_appt_booker_request_has_booking_args() ) {
		tj_appt_booker_load_engine();
	}
}, 5 );

/**
 * Shortcode gate. Runs on `wp` (after the queried post is known, before
 * template rendering and before wp_enqueue_scripts), so if the page
 * contains a booking shortcode the engine registers its shortcodes and
 * assets in time.
 */
add_action( 'wp', function () {

	if ( ! is_singular() ) {
		return;
	}

	$post = get_post();
	if ( ! $post instanceof WP_Post ) {
		return;
	}

	$tags = array( 'appt_booker', 'appt_tax_field', 'appt_term_field', 'appt_loopgrid_debug', 'appt_rebuild_loopgrid_indexes' );

	$needs_engine = false;
	foreach ( $tags as $tag ) {
		if ( has_shortcode( (string) $post->post_content, $tag ) ) {
			$needs_engine = true;
			break;
		}
	}

	// Elementor stores widget content in _elementor_data meta rather than
	// post_content, so a shortcode placed in an Elementor widget would be
	// invisible to has_shortcode(). Cheap fallback: one meta read on the
	// single queried post (already primed in the object cache).
	if ( ! $needs_engine ) {
		$elementor_data = get_post_meta( $post->ID, '_elementor_data', true );
		if ( is_string( $elementor_data ) && '' !== $elementor_data ) {
			foreach ( $tags as $tag ) {
				if ( false !== strpos( $elementor_data, $tag ) ) {
					$needs_engine = true;
					break;
				}
			}
		}
	}

	/**
	 * Escape hatch: force-load the engine on specific requests, e.g.
	 * add_filter( 'tj_appt_booker_force_load', '__return_true' );
	 */
	if ( ! $needs_engine && apply_filters( 'tj_appt_booker_force_load', false ) ) {
		$needs_engine = true;
	}

	if ( $needs_engine ) {
		tj_appt_booker_load_engine();
	}
}, 1 );

/**
 * Activation: load the engine once so its constructor runs the schema
 * install/upgrade immediately, rather than waiting for the first
 * booking-related request.
 */
register_activation_hook( __FILE__, function () {
	tj_appt_booker_load_engine();
} );

/**
 * Deactivation: clear the plugin's recurring cron events so they do not
 * fire into a void while the plugin is off. Booking data is untouched.
 */
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_hook( 'rgl_booking_daily_summary' );
	wp_clear_scheduled_hook( 'rgl_booking_retention_sweep' );
	wp_clear_scheduled_hook( 'rgl_booking_reminder_sweep' );
	wp_clear_scheduled_hook( 'rgl_booking_send_consultant_change_event' );
} );
