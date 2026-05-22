<?php
/**
 * Plugin Name: Titan Jewellery Order Numbers
 * Description: Generates Sellerdeck-style order numbers: customer initials + postcode + sequence + date.
 * Version: 1.0.0
 * Author: Titan Jewellery
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Generate a Sellerdeck-style order number.
 *
 * Format: {INITIALS}{POSTCODE_DIGITS}{ORDER_ID_PADDED}
 * Example: DL86QL10040519
 *   DL       = Dominika Lopacinska (first letters of first + last name)
 *   86QL     = NG8 6QL → strip leading letters → 86QL
 *   10040519 = sequential order ID zero-padded to 8 digits
 */
add_filter( 'woocommerce_order_number', 'titan_sellerdeck_order_number', 10, 2 );

function titan_sellerdeck_order_number( $order_id, $order ) {
    // --- 1. Initials ---
    $first = strtoupper( (string) $order->get_billing_first_name() );
    $last  = strtoupper( (string) $order->get_billing_last_name() );

    $initial_first = $first !== '' ? $first[0] : 'X';
    $initial_last  = $last  !== '' ? $last[0]  : 'X';
    $initials = $initial_first . $initial_last;

    // --- 2. Postcode: strip leading letters from the outward code ---
    // NG8 6QL → remove spaces → NG86QL → strip leading alpha chars → 86QL
    $postcode      = strtoupper( str_replace( ' ', '', (string) $order->get_billing_postcode() ) );
    $postcode_part = preg_replace( '/^[A-Z]+/', '', $postcode );

    if ( $postcode_part === '' ) {
        $postcode_part = $postcode; // fallback: use full postcode if nothing was stripped
    }

    // --- 3. Sequential order ID zero-padded to 8 digits ---
    $order_num = str_pad( (string) $order_id, 8, '0', STR_PAD_LEFT );

    return $initials . $postcode_part . $order_num;
}
