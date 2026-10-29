<?php
/**
 * Checkout — the form, its texts, payment methods, placing the order,
 * and the thank-you page helpers.
 *
 * The page body is checkoutPage.php (template "Checkout Page").
 * Prices and totals come from wearit_get_cart() / wearit_cart_totals() in inc/cart.php,
 * so they are always read on the server — the browser only ever sent keys and quantities.
 * The form posts to admin-post.php; the handler lives further down this file.
 *
 * The order post type and everything that stores an order live in inc/orders.php.
 * Nothing from that file belongs here.
 */

// ── The checkout page ─────────────────────────────────────────

/**
 * ID of the checkout page: the page using the "Checkout Page" template,
 * or else the page with slug "checkout". 0 when neither exists.
 */
function wearit_checkout_page_id() {
    static $id = null;
    if ( $id !== null ) {
        return $id;
    }

    if ( is_page_template( 'checkoutPage.php' ) ) {
        $id = get_queried_object_id();
        return $id;
    }

    $pages = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'publish',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'checkoutPage.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    if ( $pages ) {
        $id = (int) $pages[0];
        return $id;
    }

    $page = get_page_by_path( 'checkout' );
    $id   = $page ? (int) $page->ID : 0;
    return $id;
}

function wearit_checkout_url() {
    $id = wearit_checkout_page_id();
    return $id ? get_permalink( $id ) : home_url( '/checkout/' );
}

/**
 * The checkout page's text fields, per admin tab: name => [ label, fallback, field type ].
 */
function wearit_checkout_page_texts() {
    return [
        'Hero' => [
            'checkout_eyebrow'        => [ 'Eyebrow', 'Almost there', 'text' ],
            'checkout_heading'        => [ 'Heading', 'Check', 'text' ],
            'checkout_heading_accent' => [ 'Heading (lime part)', 'out', 'text' ],
        ],
        'Form' => [
            'checkout_form_title'    => [ 'Form title', 'Delivery Details', 'text' ],
            'checkout_name_label'    => [ 'Name label', 'Full name', 'text' ],
            'checkout_email_label'   => [ 'Email label', 'Email', 'text' ],
            'checkout_address_label' => [ 'Address label', 'Address', 'text' ],
            'checkout_city_label'    => [ 'City label', 'City', 'text' ],
            'checkout_post_label'    => [ 'Postcode label', 'Postcode', 'text' ],
            'checkout_country_label' => [ 'Country label', 'Country', 'text' ],
            'checkout_notes_label'   => [ 'Notes label', 'Order notes (optional)', 'text' ],
            'checkout_payment_label' => [ 'Payment heading', 'Payment Method', 'text' ],
            'checkout_submit_label'  => [ 'Submit button', 'Place Order', 'text' ],
        ],
        'Order summary' => [
            'checkout_summary_title' => [ 'Title', 'Order Summary', 'text' ],
            'checkout_back_label'    => [ 'Back button', 'Back to Cart', 'text' ],
            'checkout_payment_note'  => [ 'Note under the button', 'No card details are taken on this site.', 'textarea' ],
        ],
        'Empty cart' => [
            'checkout_empty_heading' => [ 'Heading', 'Nothing to', 'text' ],
            'checkout_empty_accent'  => [ 'Heading (lime part)', 'check out.', 'text' ],
            'checkout_empty_text'    => [ 'Text', 'Your cart is empty. Add a piece before placing an order.', 'textarea' ],
            'checkout_empty_button'  => [ 'Button label', 'Shop Products', 'text' ],
        ],
        'Thank you' => [
            'thankyou_eyebrow' => [ 'Eyebrow', 'Order placed', 'text' ],
            'thankyou_heading' => [ 'Heading', 'Thank', 'text' ],
            'thankyou_accent'  => [ 'Heading (lime part)', 'you.', 'text' ],
            'thankyou_text'    => [ 'Text', 'Your order is in. We have sent a confirmation to your email, and we will be in touch when it ships.', 'textarea' ],
            'thankyou_button'  => [ 'Button label', 'Keep Shopping', 'text' ],
            'thankyou_missing' => [ 'Text when there is no order', 'We could not find that order. If you just placed one, check your email for the confirmation.', 'textarea' ],
        ],
    ];
}

/**
 * One SCF field of the checkout page, falling back to the text from the design.
 */
function wearit_checkout_text( $name, $fallback = null ) {
    $page  = wearit_checkout_page_id();
    $value = ( $page && function_exists( 'get_field' ) ) ? get_field( $name, $page ) : '';
    if ( $value !== null && $value !== false && $value !== '' ) {
        return $value;
    }

    if ( $fallback === null ) {
        foreach ( wearit_checkout_page_texts() as $group ) {
            if ( isset( $group[ $name ] ) ) {
                return $group[ $name ][1];
            }
        }
        return '';
    }
    return $fallback;
}

// ── Payment methods ───────────────────────────────────────────

/**
 * The methods the customer can pick.
 *
 * No card details are taken on this site. A card payment would go through a
 * provider's own hosted page (Stripe Checkout), which is why 'card' below is a
 * redirect method with no fields of its own — add the provider and flip 'enabled'.
 *
 * Each method:
 *   label, description   what the customer sees on the checkout page
 *   instructions         what the thank-you page and the email tell them to do
 *   settles              'on_delivery' | 'before_shipping' | 'immediate'
 *                        when the money is expected, which decides what happens next
 *   needs_reference      true when the customer must quote the order number when paying
 *   fee                  flat surcharge in dollars, 0 for none
 *   enabled              false hides it from the checkout page
 */
function wearit_payment_methods() {
    return [
        'cod' => [
            'label'           => 'Cash on delivery',
            'description'     => 'Pay the courier when your order arrives.',
            'instructions'    => 'Have the total ready in cash when the courier arrives. They cannot give change for large notes.',
            'settles'         => 'on_delivery',
            'needs_reference' => false,
            'fee'             => 3.00,
            'enabled'         => true,
        ],
        'bank' => [
            'label'           => 'Bank transfer',
            'description'     => 'We ship as soon as the transfer lands, usually one working day.',
            'instructions'    => 'Transfer the total to WearIt Ltd, IBAN GB00 WEAR 0000 0000 0000 00. Use your order number as the payment reference or we cannot match it to your order.',
            'settles'         => 'before_shipping',
            'needs_reference' => true,
            'fee'             => 0,
            'enabled'         => true,
        ],
        'card' => [
            'label'           => 'Card',
            'description'     => 'Pay by card through our payment provider.',
            'instructions'    => 'Your card payment was taken by our provider. Nothing further to do.',
            'settles'         => 'immediate',
            'needs_reference' => false,
            'fee'             => 0,
            'enabled'         => false, // turn on once a provider is wired up
        ],
    ];
}

/** Only the methods a customer can actually pick right now. */
function wearit_enabled_payment_methods() {
    return array_filter( wearit_payment_methods(), function ( $method ) {
        return ! empty( $method['enabled'] );
    } );
}

/** One method's settings, or the cash-on-delivery ones as a fallback. */
function wearit_payment_method( $key ) {
    $methods = wearit_payment_methods();
    return $methods[ $key ] ?? $methods['cod'];
}

function wearit_payment_label( $key ) {
    return wearit_payment_method( $key )['label'];
}

function wearit_payment_instructions( $key ) {
    return wearit_payment_method( $key )['instructions'];
}

/** The surcharge this method adds, if any. */
function wearit_payment_fee( $key ) {
    return (float) wearit_payment_method( $key )['fee'];
}

// ── Checkout page fields (in code, same as the cart page) ─────

add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = [];
    foreach ( wearit_checkout_page_texts() as $tab => $group ) {
        $fields[] = [
            'key'   => 'field_wearit_checkout_tab_' . sanitize_key( $tab ),
            'label' => $tab,
            'type'  => 'tab',
        ];
        foreach ( $group as $name => [ $label, $fallback, $type ] ) {
            $fields[] = [
                'key'          => 'field_wearit_' . $name,
                'label'        => $label,
                'name'         => $name,
                'type'         => $type,
                'placeholder'  => $fallback,
                'instructions' => 'Leave empty to use: ' . $fallback,
                'rows'         => 3,
            ];
        }
    }

    acf_add_local_field_group( [
        'key'      => 'group_wearit_checkout_page',
        'title'    => 'Checkout page',
        'fields'   => $fields,
        'location' => [
            [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'checkoutPage.php' ] ],
            [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'thankYouPage.php' ] ],
        ],
        'position'       => 'normal',
        'menu_order'     => 0,
        'hide_on_screen' => [ 'the_content' ],
    ] );
} );

// ── Placing the order ─────────────────────────────────────────

add_action( 'admin_post_wearit_checkout', 'wearit_handle_checkout' );
add_action( 'admin_post_nopriv_wearit_checkout', 'wearit_handle_checkout' ); // hooked so logged-out posts get sent to log in, not a blank page

function wearit_handle_checkout() {
    error_log( 'wearit_checkout: handler fired' );

    $redirect = wearit_checkout_url();

    // Only customers with an account can order (inc/account.php).
    if ( ! is_user_logged_in() ) {
        error_log( 'wearit_checkout: not logged in' );
        wp_safe_redirect( wearit_account_url( $redirect ) );
        exit;
    }

    if ( ! isset( $_POST['wearit_checkout_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_checkout_nonce'], 'wearit_checkout_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }

    // What the customer typed. Everything else — prices, fees, titles — is read from the server below.
    $customer = [
        'name'     => sanitize_text_field( wp_unslash( $_POST['checkout_name'] ?? '' ) ),
        'email'    => sanitize_email( wp_unslash( $_POST['checkout_email'] ?? '' ) ),
        'address'  => sanitize_text_field( wp_unslash( $_POST['checkout_address'] ?? '' ) ),
        'city'     => sanitize_text_field( wp_unslash( $_POST['checkout_city'] ?? '' ) ),
        'postcode' => sanitize_text_field( wp_unslash( $_POST['checkout_postcode'] ?? '' ) ),
        'country'  => sanitize_text_field( wp_unslash( $_POST['checkout_country'] ?? '' ) ),
        'notes'    => sanitize_textarea_field( wp_unslash( $_POST['checkout_notes'] ?? '' ) ),
        'payment'  => sanitize_key( wp_unslash( $_POST['checkout_payment'] ?? '' ) ),
    ];

    // Only a method we actually offer. Anything else falls back to cash on delivery.
    if ( ! isset( wearit_enabled_payment_methods()[ $customer['payment'] ] ) ) {
        $customer['payment'] = 'cod';
    }

    // Every field except notes has to be filled in.
    $required = [ 'name', 'address', 'city', 'postcode', 'country' ];
    foreach ( $required as $field ) {
        if ( $customer[ $field ] === '' ) {
            error_log( 'wearit_checkout: missing ' . $field );
            wp_safe_redirect( add_query_arg( 'checkout', 'invalid', $redirect ) );
            exit;
        }
    }
    if ( ! is_email( $customer['email'] ) ) {
        error_log( 'wearit_checkout: bad email' );
        wp_safe_redirect( add_query_arg( 'checkout', 'invalid', $redirect ) );
        exit;
    }

    // The cart is read here, on the server, from the cookie's keys and quantities.
    // Nothing about prices was posted, so an edited form can't change what anything costs.
    $cart = wearit_get_cart();
    if ( ! $cart['items'] ) {
        error_log( 'wearit_checkout: cart empty' );
        wp_safe_redirect( add_query_arg( 'checkout', 'empty', $redirect ) );
        exit;
    }
    $totals = wearit_cart_totals( $cart );

    // The method's surcharge is added here, on the server, like every other price.
    $fee              = wearit_payment_fee( $customer['payment'] );
    $totals['total'] += $fee;

    // Flatten the cart into the line items the order stores.
    // Size isn't in the cart keys yet, so it stays empty for now bleh
    $items = [];
    foreach ( $cart['items'] as $item ) {
        $items[] = [
            'id'         => $item['key'],
            'title'      => $item['title'],
            'sku'        => '',
            'size'       => '',
            'qty'        => $item['qty'],
            'price'      => $item['price'],
            'line_total' => $item['line_total'],
        ];
    }

    // Just data being handed to orders.php — no statements inside this array.
    $order_id = wearit_create_order( $customer, $items, [
        'subtotal' => $cart['subtotal'],
        'shipping' => $totals['shipping'],
        'fee'      => $fee,
        'total'    => $totals['total'],
    ] );

    if ( ! $order_id ) {
        error_log( 'wearit_checkout: order could not be created' );
        wp_safe_redirect( add_query_arg( 'checkout', 'error', $redirect ) );
        exit;
    }

    error_log( 'wearit_checkout: order ' . $order_id . ' created' );

    // The order is saved, so the cart can go.
    wearit_cart_save_raw( [] );

    // A key the thank-you page checks, so order numbers can't just be typed into the URL.
    $key = wp_hash( 'wearit_order_' . $order_id );
    update_post_meta( $order_id, '_wearit_key', $key );

    // Redirect instead of printing here, so refreshing doesn't place a second order.
    wp_safe_redirect( add_query_arg( [
        'order' => $order_id,
        'key'   => $key,
    ], wearit_thankyou_url() ) );
    exit;
}

// ── Thank-you page ────────────────────────────────────────────

/**
 * ID of the thank-you page: the page using the "Thank You Page" template,
 * or else the page with slug "thank-you". 0 when neither exists.
 */
function wearit_thankyou_page_id() {
    static $id = null;
    if ( $id !== null ) {
        return $id;
    }

    if ( is_page_template( 'thankYouPage.php' ) ) {
        $id = get_queried_object_id();
        return $id;
    }

    $pages = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'publish',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'thankYouPage.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    if ( $pages ) {
        $id = (int) $pages[0];
        return $id;
    }

    $page = get_page_by_path( 'thank-you' );
    $id   = $page ? (int) $page->ID : 0;
    return $id;
}

function wearit_thankyou_url() {
    $id = wearit_thankyou_page_id();
    return $id ? get_permalink( $id ) : home_url( '/thank-you/' );
}

/**
 * The order the thank-you page should show, from ?order= and ?key=.
 * Null when the key doesn't match, so order numbers can't be guessed.
 */
function wearit_thankyou_order() {
    $order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
    $key      = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';
    if ( ! $order_id || ! $key ) {
        return null;
    }

    $stored = get_post_meta( $order_id, '_wearit_key', true );
    if ( ! $stored || ! hash_equals( (string) $stored, $key ) ) {
        return null;
    }

    return wearit_get_order( $order_id );
}