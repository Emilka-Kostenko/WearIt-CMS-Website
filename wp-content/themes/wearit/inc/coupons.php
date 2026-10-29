<?php
/**
 * Coupons — codes, checking them, the discount, and the welcome popup.
 *
 *   WEARIT10   10% off the products (not shipping or payment fees), once per account.
 *              Every new account is told about it in a popup right after signing up.
 *
 * Checkout needs an account (inc/account.php), so everything here is per user:
 *   _wearit_applied_coupon   user meta: the code applied on the checkout page right now
 *   _wearit_coupons_used     user meta: codes this account has already used on an order
 *   _wearit_welcome_coupon   user meta: 1 until the welcome popup has been shown
 *
 * The discount is always worked out on the server from the cart, like every other price.
 * A code is only marked as used once the order is actually saved (wearit_handle_checkout()).
 */

// ── The codes ─────────────────────────────────────────────────

/**
 * Every code the shop accepts: CODE => settings.
 *   percent   discount on the products subtotal
 *   label     how it is described to the customer
 */
function wearit_coupons() {
    return [
        'WEARIT10' => [
            'percent' => 10,
            'label'   => '10% off',
        ],
    ];
}

/** "  wearit10 " → "WEARIT10" */
function wearit_coupon_normalize( $code ) {
    return strtoupper( trim( preg_replace( '/\s+/', '', (string) $code ) ) );
}

/** The codes this account has used on an order. */
function wearit_coupons_used( $user_id ) {
    $used = get_user_meta( $user_id, '_wearit_coupons_used', true );
    return is_array( $used ) ? $used : [];
}

/**
 * Can this account use this code? True, or a WP_Error whose code is the
 * ?coupon= message key (see wearit_coupon_notice()).
 */
function wearit_coupon_check( $code, $user_id ) {
    $code = wearit_coupon_normalize( $code );
    if ( $code === '' ) {
        return new WP_Error( 'empty' );
    }
    if ( ! isset( wearit_coupons()[ $code ] ) ) {
        return new WP_Error( 'invalid' );
    }
    if ( ! $user_id ) {
        return new WP_Error( 'login' );
    }
    if ( in_array( $code, wearit_coupons_used( $user_id ), true ) ) {
        return new WP_Error( 'used' );
    }
    return true;
}

/**
 * The code applied on the checkout page for this account, or '' — checked again
 * every time, so a code used on another order in the meantime drops off.
 */
function wearit_applied_coupon( $user_id ) {
    if ( ! $user_id ) {
        return '';
    }
    $code = (string) get_user_meta( $user_id, '_wearit_applied_coupon', true );
    if ( $code === '' ) {
        return '';
    }
    if ( wearit_coupon_check( $code, $user_id ) !== true ) {
        delete_user_meta( $user_id, '_wearit_applied_coupon' );
        return '';
    }
    return $code;
}

/** The discount for $subtotal, rounded to cents. */
function wearit_coupon_discount( $code, $subtotal ) {
    $coupon = wearit_coupons()[ wearit_coupon_normalize( $code ) ] ?? null;
    if ( ! $coupon ) {
        return 0.0;
    }
    return round( (float) $subtotal * $coupon['percent'] / 100, 2 );
}

/**
 * wearit_cart_totals() plus the coupon: what the checkout page shows and the order stores.
 * The payment method's fee is added on top when the order is placed.
 *
 * @return array [ 'shipping', 'discount', 'coupon' (code or ''), 'total' ]
 */
function wearit_checkout_totals( array $cart, $user_id = null ) {
    $user_id = $user_id ?? get_current_user_id();
    $totals  = wearit_cart_totals( $cart );
    $code    = $cart['items'] ? wearit_applied_coupon( $user_id ) : '';

    $totals['coupon']   = $code;
    $totals['discount'] = $code ? wearit_coupon_discount( $code, $cart['subtotal'] ) : 0.0;
    $totals['total']    = max( 0, $totals['total'] - $totals['discount'] );
    return $totals;
}

/**
 * After an order is saved: the code counts as used and comes off the checkout page.
 */
function wearit_coupon_mark_used( $code, $user_id ) {
    $used   = wearit_coupons_used( $user_id );
    $used[] = wearit_coupon_normalize( $code );
    update_user_meta( $user_id, '_wearit_coupons_used', array_values( array_unique( $used ) ) );
    delete_user_meta( $user_id, '_wearit_applied_coupon' );
}

/**
 * The message for ?coupon=… on the checkout page.
 *
 * @return array|null [ 'type' => 'error'|'success', 'text' => string ]
 */
function wearit_coupon_notice() {
    $key  = isset( $_GET['coupon'] ) ? sanitize_key( $_GET['coupon'] ) : '';
    $code = 'WEARIT10'; // the only code there is; named in the "already used" message
    $notices = [
        'applied' => [ 'success', 'Code applied. The discount is in the total below.' ],
        'removed' => [ 'success', 'Code removed.' ],
        'empty'   => [ 'error', 'Type a coupon code first, then press Apply.' ],
        'invalid' => [ 'error', 'That code isn’t valid. Check the spelling and try again.' ],
        'used'    => [ 'error', 'You have already used this code on an earlier order. {code} works once per account.' ],
        'login'   => [ 'error', 'Log in to use a coupon code.' ],
    ];
    if ( ! isset( $notices[ $key ] ) ) {
        return null;
    }
    $text = wearit_checkout_text( 'checkout_coupon_msg_' . $key, $notices[ $key ][1] );
    return [ 'type' => $notices[ $key ][0], 'text' => strtr( $text, [ '{code}' => $code ] ) ];
}

// ── Apply / remove (forms on the checkout page) ───────────────

add_action( 'admin_post_wearit_apply_coupon', 'wearit_handle_apply_coupon' );
add_action( 'admin_post_nopriv_wearit_apply_coupon', 'wearit_handle_apply_coupon' );
add_action( 'admin_post_wearit_remove_coupon', 'wearit_handle_remove_coupon' );
add_action( 'admin_post_nopriv_wearit_remove_coupon', 'wearit_handle_remove_coupon' );

/** Back to the coupon box on the checkout page with ?coupon=$key. */
function wearit_coupon_bounce( $key ) {
    wp_safe_redirect( add_query_arg( 'coupon', $key, wearit_checkout_url() ) . '#checkout-coupon' );
    exit;
}

function wearit_handle_apply_coupon() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( wearit_account_url( wearit_checkout_url() ) );
        exit;
    }
    if ( ! isset( $_POST['wearit_coupon_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_coupon_nonce'], 'wearit_coupon_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }

    $user_id = get_current_user_id();
    $code    = wearit_coupon_normalize( wp_unslash( $_POST['coupon_code'] ?? '' ) );
    $check   = wearit_coupon_check( $code, $user_id );

    if ( is_wp_error( $check ) ) {
        wearit_coupon_bounce( $check->get_error_code() );
    }

    update_user_meta( $user_id, '_wearit_applied_coupon', $code );
    wearit_coupon_bounce( 'applied' );
}

function wearit_handle_remove_coupon() {
    if ( ! is_user_logged_in() ) {
        wp_safe_redirect( wearit_account_url( wearit_checkout_url() ) );
        exit;
    }
    if ( ! isset( $_POST['wearit_coupon_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_coupon_nonce'], 'wearit_coupon_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }

    delete_user_meta( get_current_user_id(), '_wearit_applied_coupon' );
    wearit_coupon_bounce( 'removed' );
}

// ── Welcome popup after signing up ────────────────────────────

/** Called by wearit_handle_register() once the account exists. */
function wearit_coupon_welcome( $user_id ) {
    update_user_meta( $user_id, '_wearit_welcome_coupon', 1 );
}

// Shown once, on whatever page the new customer lands on after signing up.
add_action( 'wp_footer', function () {
    $user_id = get_current_user_id();
    if ( ! $user_id || ! get_user_meta( $user_id, '_wearit_welcome_coupon', true ) ) {
        return;
    }
    delete_user_meta( $user_id, '_wearit_welcome_coupon' );
    get_template_part( 'template-parts/welcome-coupon', null, [ 'code' => 'WEARIT10' ] );
} );
