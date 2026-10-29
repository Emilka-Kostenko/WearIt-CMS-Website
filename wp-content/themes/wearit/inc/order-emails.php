<?php
/**
 * Order emails — confirmation to the customer, notification to the shop.
 *
 * Hooked onto the order being created, not onto the checkout handler, so an order
 * made any other way (the admin, a future card flow) still sends the same emails.
 * Plain text on purpose: it always renders, and nothing here needs a layout.
 *
 * wp_mail quietly fails on most local setups because there is no mail server.
 * Install "WP Mail Logging" to read what would have been sent.
 */

// ── Helpers ───────────────────────────────────────────────────

/** Who the emails come from. */
function wearit_email_headers( $reply_to = '' ) {
    $headers = [ 'Content-Type: text/plain; charset=UTF-8' ];
    if ( $reply_to ) {
        $headers[] = 'Reply-To: ' . $reply_to;
    }
    return $headers;
}

/** The items and totals as a plain text block, used in both emails. */
function wearit_email_order_lines( array $order ) {
    $lines = '';

    foreach ( $order['items'] as $item ) {
        $lines .= sprintf(
            "%s x%d — %s\n",
            $item['title'] ?? '',
            $item['qty'] ?? 0,
            wearit_cart_format_price( $item['line_total'] ?? 0 )
        );
    }

    $lines .= "\n";
    $lines .= 'Subtotal: ' . wearit_cart_format_price( $order['subtotal'] ) . "\n";
    if ( $order['discount'] > 0 ) {
        $lines .= wearit_checkout_text( 'checkout_discount_label', 'Discount' ) . ' (' . $order['coupon'] . '): -' . wearit_cart_format_price( $order['discount'] ) . "\n";
    }
    $lines .= 'Shipping: ' . ( $order['shipping'] > 0 ? wearit_cart_format_price( $order['shipping'] ) : 'Free' ) . "\n";

    if ( $order['fee'] > 0 ) {
        $lines .= wearit_payment_label( $order['payment'] ) . ' fee: ' . wearit_cart_format_price( $order['fee'] ) . "\n";
    }

    $lines .= 'Total: ' . wearit_cart_format_price( $order['total'] ) . "\n";

    return $lines;
}

/** The delivery address as a plain text block. */
function wearit_email_address( array $order ) {
    return $order['name'] . "\n"
        . $order['address'] . "\n"
        . $order['postcode'] . ' ' . $order['city'] . "\n"
        . $order['country'] . "\n";
}

// ── The customer's confirmation ───────────────────────────────

function wearit_send_customer_email( $order_id ) {

    $order = wearit_get_order( $order_id );
    if ( ! $order || ! is_email( $order['email'] ) ) {
        error_log( 'wearit_email: no valid customer address on order ' . $order_id );
        return false;
    }

    $shop    = get_bloginfo( 'name' );
    $subject = $shop . ' — order ' . $order['reference'];

    $body  = 'Thanks for your order, ' . $order['name'] . ".\n\n";
    $body .= 'Order ' . $order['reference'] . ', placed ' . $order['date'] . ".\n\n";

    $body .= "WHAT YOU ORDERED\n";
    $body .= wearit_email_order_lines( $order ) . "\n";

    $body .= "DELIVERING TO\n";
    $body .= wearit_email_address( $order ) . "\n";

    $body .= "PAYMENT — " . strtoupper( wearit_payment_label( $order['payment'] ) ) . "\n";
    $body .= wearit_payment_instructions( $order['payment'] ) . "\n";

    // Bank transfers need the reference quoted or we can't match the money to the order.
    if ( ! empty( wearit_payment_method( $order['payment'] )['needs_reference'] ) ) {
        $body .= 'Payment reference: ' . $order['reference'] . "\n";
    }

    if ( $order['notes'] ) {
        $body .= "\nYOUR NOTES\n" . $order['notes'] . "\n";
    }

    $body .= "\nWe will email you again when it ships.\n";
    $body .= $shop . ' — ' . home_url( '/' ) . "\n";

    $sent = wp_mail( $order['email'], $subject, $body, wearit_email_headers( get_option( 'admin_email' ) ) );
    error_log( 'wearit_email: customer mail for ' . $order['reference'] . ' returned ' . var_export( $sent, true ) );

    return $sent;
}

// ── The shop's notification ───────────────────────────────────

function wearit_send_admin_email( $order_id ) {

    $order = wearit_get_order( $order_id );
    if ( ! $order ) {
        return false;
    }

    $subject = '[New order] ' . $order['reference'] . ' — ' . wearit_cart_format_price( $order['total'] );

    $body  = 'New order ' . $order['reference'] . ', ' . $order['date'] . ".\n\n";
    $body .= 'Payment: ' . wearit_payment_label( $order['payment'] ) . "\n\n";

    $body .= "ITEMS\n";
    $body .= wearit_email_order_lines( $order ) . "\n";

    $body .= "CUSTOMER\n";
    $body .= wearit_email_address( $order );
    $body .= $order['email'] . "\n";

    if ( $order['notes'] ) {
        $body .= "\nNOTES\n" . $order['notes'] . "\n";
    }

    $body .= "\nOpen it: " . admin_url( 'post.php?post=' . $order['id'] . '&action=edit' ) . "\n";

    // Reply-To is the customer, so hitting reply in the inbox goes to them.
    $sent = wp_mail( get_option( 'admin_email' ), $subject, $body, wearit_email_headers( $order['email'] ) );
    error_log( 'wearit_email: admin mail for ' . $order['reference'] . ' returned ' . var_export( $sent, true ) );

    return $sent;
}

// ── Send both when an order is placed ─────────────────────────

add_action( 'wearit_order_created', 'wearit_send_customer_email', 10, 1 );
add_action( 'wearit_order_created', 'wearit_send_admin_email', 10, 1 );

// ── Tell the customer when it ships ───────────────────────────

add_action( 'wearit_order_status_changed', function ( $order_id, $status, $before ) {

    if ( $status !== 'shipped' ) {
        return;
    }

    $order = wearit_get_order( $order_id );
    if ( ! $order || ! is_email( $order['email'] ) ) {
        return;
    }

    $shop    = get_bloginfo( 'name' );
    $subject = $shop . ' — order ' . $order['reference'] . ' has shipped';

    $body  = 'Good news, ' . $order['name'] . ".\n\n";
    $body .= 'Order ' . $order['reference'] . " is on its way.\n\n";
    $body .= "DELIVERING TO\n" . wearit_email_address( $order ) . "\n";

    if ( $order['payment'] === 'cod' ) {
        $body .= 'Remember: ' . wearit_payment_instructions( 'cod' ) . "\n\n";
    }

    $body .= $shop . ' — ' . home_url( '/' ) . "\n";

    $sent = wp_mail( $order['email'], $subject, $body, wearit_email_headers( get_option( 'admin_email' ) ) );
    error_log( 'wearit_email: shipped mail for ' . $order['reference'] . ' returned ' . var_export( $sent, true ) );

    wearit_order_log( $order_id, 'Shipping confirmation emailed' );

}, 10, 3 );