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
 * Format: {INITIALS}{POSTCODE_DIGITS}{ORDER_ID_PADDED}{MMDD}
 * Example: DL86QL10040519
 *   DL    = Dominika Lopacinska (first letters of first + last name)
 *   86QL  = NG8 6QL → strip leading letters → 86QL
 *   1004  = order ID zero-padded to 4 digits
 *   0519  = MMDD (UTC date of order creation)
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

    // --- 3. Order sequence: zero-padded to 4 digits ---
    $order_num = str_pad( (string) $order_id, 4, '0', STR_PAD_LEFT );

    // --- 4. Date in MMDD format (UTC, matching Sellerdeck behaviour) ---
    $date_created = $order->get_date_created();
    $date_part    = $date_created ? $date_created->format( 'md' ) : gmdate( 'md' );

    return $initials . $postcode_part . $order_num . $date_part;
}
