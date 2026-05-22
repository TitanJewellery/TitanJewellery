<?php
/**
 * Plugin Name: Titan Jewellery Order Numbers
 * Description: Generates Sellerdeck-style order numbers: customer initials + postcode + sequential number.
 * Version: 1.1.0
 * Author: Titan Jewellery
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'TITAN_ORDER_SEQ_START', 10051000 );
define( 'TITAN_ORDER_SEQ_OPTION', 'titan_order_sequence' );

/**
 * Assign a sequential number to the order at creation time and store it as meta.
 * Uses MySQL LAST_INSERT_ID() trick for an atomic, race-condition-safe increment.
 *
 * Hooked into both classic checkout and block-based checkout.
 */
add_action( 'woocommerce_checkout_order_created',                'titan_assign_order_sequence' );
add_action( 'woocommerce_store_api_checkout_order_processed',    'titan_assign_order_sequence' );

function titan_assign_order_sequence( $order ) {
    // Only assign once — skip if already set (e.g. hook fires twice).
    if ( $order->get_meta( '_titan_order_sequence' ) ) {
        return;
    }

    global $wpdb;

    // Seed the option on first ever use without overwriting an existing value.
    add_option( TITAN_ORDER_SEQ_OPTION, TITAN_ORDER_SEQ_START - 1, '', 'no' );

    // Atomically increment the counter and retrieve the new value.
    $wpdb->query(
        $wpdb->prepare(
            "UPDATE {$wpdb->options}
             SET option_value = LAST_INSERT_ID(option_value + 1)
             WHERE option_name = %s",
            TITAN_ORDER_SEQ_OPTION
        )
    );

    $sequence = (int) $wpdb->get_var( "SELECT LAST_INSERT_ID()" );

    $order->update_meta_data( '_titan_order_sequence', $sequence );
    $order->save_meta_data();
}

/**
 * Display the Sellerdeck-style order number.
 *
 * Format: {INITIALS}{POSTCODE_DIGITS}{SEQUENCE}
 * Example: DL86QL10051000
 *   DL       = Dominika Lopacinska
 *   86QL     = NG8 6QL → strip leading letters → 86QL
 *   10051000 = sequential number starting at 10051000
 */
add_filter( 'woocommerce_order_number', 'titan_sellerdeck_order_number', 10, 2 );

function titan_sellerdeck_order_number( $order_id, $order ) {
    // --- 1. Initials ---
    $first = strtoupper( (string) $order->get_billing_first_name() );
    $last  = strtoupper( (string) $order->get_billing_last_name() );

    $initial_first = $first !== '' ? $first[0] : 'X';
    $initial_last  = $last  !== '' ? $last[0]  : 'X';
    $initials      = $initial_first . $initial_last;

    // --- 2. Postcode: strip leading letters from the outward code ---
    // NG8 6QL → remove spaces → NG86QL → strip leading alpha chars → 86QL
    $postcode      = strtoupper( str_replace( ' ', '', (string) $order->get_billing_postcode() ) );
    $postcode_part = preg_replace( '/^[A-Z]+/', '', $postcode );

    if ( $postcode_part === '' ) {
        $postcode_part = $postcode;
    }

    // --- 3. Sequential number (stored on order at creation) ---
    $sequence = $order->get_meta( '_titan_order_sequence' );

    if ( ! $sequence ) {
        // Fallback for orders placed before this plugin was active.
        $sequence = str_pad( (string) $order_id, 8, '0', STR_PAD_LEFT );
    }

    return $initials . $postcode_part . $sequence;
}
