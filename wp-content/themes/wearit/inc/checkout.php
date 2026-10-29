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
                return __( $group[ $name ][1], 'wearit' );
            }
        }
        return '';
    }
    return is_string( $fallback ) ? __( $fallback, 'wearit' ) : $fallback;
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
 *   fee                  flat surcharge in euros, 0 for none
 *   enabled              false hides it from the checkout page
 */
function wearit_payment_methods() {
    return [
        'cod' => [
            'label'           => __( 'Cash on delivery', 'wearit' ),
            'description'     => __( 'Pay the courier when your order arrives.', 'wearit' ),
            'instructions'    => __( 'Have the total ready in cash when the courier arrives. They cannot give change for large notes.', 'wearit' ),
            'settles'         => 'on_delivery',
            'needs_reference' => false,
            'fee'             => 3.00,
            'enabled'         => true,
        ],
        'bank' => [
            'label'           => __( 'Bank transfer', 'wearit' ),
            'description'     => __( 'We ship as soon as the transfer lands, usually one working day.', 'wearit' ),
            'instructions'    => __( 'Transfer the total to WearIt Ltd, IBAN GB00 WEAR 0000 0000 0000 00. Use your order number as the payment reference or we cannot match it to your order.', 'wearit' ),
            'settles'         => 'before_shipping',
            'needs_reference' => true,
            'fee'             => 0,
            'enabled'         => true,
        ],
        'card' => [
            'label'           => __( 'Card', 'wearit' ),
            'description'     => __( 'Pay by card through our payment provider.', 'wearit' ),
            'instructions'    => __( 'Your card payment was taken by our provider. Nothing further to do.', 'wearit' ),
            'settles'         => 'immediate',
            'needs_reference' => false,
            'fee'             => 0,
            'enabled'         => wearit_stripe_is_configured(), // on once both Stripe keys are set
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
        wp_die( esc_html( __( 'Security check failed.', 'wearit' ) ) );
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

    // Card is paid in the Stripe form on the page, never through this form.
    if ( $customer['payment'] === 'card' ) {
        wp_safe_redirect( add_query_arg( 'checkout', 'invalid', $redirect ) );
        exit;
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
    // Shipping + the coupon applied on the checkout page, checked again here (inc/coupons.php).
    $totals = wearit_checkout_totals( $cart, get_current_user_id() );

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
        'discount' => $totals['discount'],
        'coupon'   => $totals['coupon'],
        'total'    => $totals['total'],
    ] );

    if ( ! $order_id ) {
        error_log( 'wearit_checkout: order could not be created' );
        wp_safe_redirect( add_query_arg( 'checkout', 'error', $redirect ) );
        exit;
    }

    error_log( 'wearit_checkout: order ' . $order_id . ' created' );

    // The order is saved, so the coupon is used up for this account.
    if ( $totals['coupon'] ) {
        wearit_coupon_mark_used( $totals['coupon'], get_current_user_id() );
    }

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

// ── Card payments (Stripe Checkout, embedded form) ────────────

const WEARIT_STRIPE_API_VERSION = '2026-03-25.dahlia';

/**
 * A Stripe key: the constant of that name from wp-config.php, or else the
 * environment variable. Keys never live in the theme.
 */
function wearit_stripe_key( $name ) {
    if ( defined( $name ) ) {
        return (string) constant( $name );
    }
    return (string) getenv( $name );
}

function wearit_stripe_is_configured() {
    return wearit_stripe_key( 'STRIPE_SECRET_KEY' ) !== '' && wearit_stripe_key( 'STRIPE_PUBLISHABLE_KEY' ) !== '';
}

/**
 * POST /wp-json/wearit/v1/checkout/session
 * Creates a Checkout Session and returns { client_secret } for the embedded form.
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'wearit/v1', '/checkout/session', [
        'methods'             => 'POST',
        'callback'            => 'wearit_rest_checkout_session',
        'permission_callback' => '__return_true',
    ] );
} );

function wearit_rest_checkout_session() {
    if ( ! wearit_stripe_is_configured() ) {
        return new WP_Error( 'wearit_stripe_missing', __( 'Card payments are not set up.', 'wearit' ), [ 'status' => 500 ] );
    }
    if ( ! is_user_logged_in() ) {
        return new WP_Error( 'wearit_login_required', __( 'Log in to check out.', 'wearit' ), [ 'status' => 401 ] );
    }

    // The cart is read here, on the server, same as wearit_handle_checkout() — the browser sends nothing.
    $cart = wearit_get_cart();
    if ( ! $cart['items'] ) {
        return new WP_Error( 'wearit_cart_empty', 'Your cart is empty.', [ 'status' => 400 ] );
    }
    $totals = wearit_checkout_totals( $cart, get_current_user_id() );

    // Prices are in euros on the site; Stripe wants cents.
    $line_items = [];
    foreach ( $cart['items'] as $item ) {
        $line_items[] = [
            'quantity'   => $item['qty'],
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => (int) round( $item['price'] * 100 ),
                'product_data' => [
                    'name'     => $item['title'],
                    'metadata' => [ 'cart_key' => $item['key'] ], // so a webhook can tell which product it was
                ],
            ],
        ];
    }

    $mode = 'payment'; // one-time purchases; "subscription" would need recurring prices

    // Booleans are sent as 'true' / 'false' strings — http_build_query would turn them into 1 / 0.
    $params = [
        'ui_mode'                     => 'form',
        'mode'                        => $mode,
        'line_items'                  => $line_items,
        'billing_address_collection'  => 'auto',
        'phone_number_collection'     => [ 'enabled' => 'false' ],
        'automatic_tax'               => [ 'enabled' => 'false' ],
        'submit_type'                 => 'auto',
        'shipping_address_collection' => [ 'allowed_countries' => [ 'DK' ] ],
        'name_collection'             => [ 'individual' => [ 'enabled' => 'true', 'optional' => 'true' ] ],
        'integration_identifier'      => 'custom_embedded_web_0002',
        'return_url'                  => add_query_arg( 'session_id', '{CHECKOUT_SESSION_ID}', wearit_thankyou_url() ),
        'metadata'                    => [ 'user_id' => get_current_user_id(), 'coupon' => $totals['coupon'] ],
    ];
    if ( $totals['coupon'] ) {
        if ( ! wearit_stripe_ensure_coupon( $totals['coupon'] ) ) {
            return new WP_Error( 'wearit_stripe_coupon', __( 'Could not apply the coupon to the card payment.', 'wearit' ), [ 'status' => 502 ] );
        }
        $params['discounts'] = [ [ 'coupon' => $totals['coupon'] ] ];
    }
    if ( $mode === 'subscription' ) {
        $params['payment_method_collection'] = 'always';
    }

    // Same shipping as the order summary shows (wearit_cart_totals()), free when it's 0.
    $params['shipping_options'] = [ [
        'shipping_rate_data' => [
            'display_name' => $totals['shipping'] > 0 ? __( 'Shipping', 'wearit' ) : __( 'Free shipping', 'wearit' ),
            'type'         => 'fixed_amount',
            'fixed_amount' => [
                'amount'   => (int) round( $totals['shipping'] * 100 ),
                'currency' => 'eur',
            ],
        ],
    ] ];

    $response = wp_remote_post( 'https://api.stripe.com/v1/checkout/sessions', [
        'headers' => [
            'Authorization'  => 'Bearer ' . wearit_stripe_key( 'STRIPE_SECRET_KEY' ),
            'Stripe-Version' => WEARIT_STRIPE_API_VERSION,
        ],
        'body'    => http_build_query( $params ),
        'timeout' => 30,
    ] );

    $session = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $session['client_secret'] ) ) {
        error_log( 'wearit_stripe: session failed — ' . ( is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ) ) );
        return new WP_Error( 'wearit_stripe_failed', __( 'Could not start the card payment.', 'wearit' ), [ 'status' => 502 ] );
    }

    return rest_ensure_response( [ 'client_secret' => $session['client_secret'] ] );
}

/** GET from the Stripe API. The decoded JSON, or null on any error. */
function wearit_stripe_get( $path, array $query = [] ) {
    $url      = 'https://api.stripe.com/v1/' . $path . ( $query ? '?' . http_build_query( $query ) : '' );
    $response = wp_remote_get( $url, [
        'headers' => [
            'Authorization'  => 'Bearer ' . wearit_stripe_key( 'STRIPE_SECRET_KEY' ),
            'Stripe-Version' => WEARIT_STRIPE_API_VERSION,
        ],
        'timeout' => 30,
    ] );

    $data = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
    if ( ! is_array( $data ) || isset( $data['error'] ) ) {
        error_log( 'wearit_stripe: GET ' . $path . ' failed — ' . ( is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ) ) );
        return null;
    }
    return $data;
}

function wearit_stripe_ensure_coupon( $code ) {
    $coupon = wearit_coupons()[ $code ] ?? null;
    if ( ! $coupon ) {
        return false;
    }

    $flag = 'wearit_stripe_coupon_' . strtolower( $code ) . '_' . substr( md5( wearit_stripe_key( 'STRIPE_SECRET_KEY' ) ), 0, 8 );
    if ( get_transient( $flag ) ) {
        return true;
    }

    $existing = wearit_stripe_get( 'coupons/' . rawurlencode( $code ) );
    if ( $existing && ! empty( $existing['valid'] ) ) {
        set_transient( $flag, 1, DAY_IN_SECONDS );
        return true;
    }

    $response = wp_remote_post( 'https://api.stripe.com/v1/coupons', [
        'headers' => [
            'Authorization'  => 'Bearer ' . wearit_stripe_key( 'STRIPE_SECRET_KEY' ),
            'Stripe-Version' => WEARIT_STRIPE_API_VERSION,
        ],
        'body'    => http_build_query( [
            'id'          => $code,
            'name'        => 'WearIt ' . $coupon['label'],
            'percent_off' => $coupon['percent'],
            'duration'    => 'once',
        ] ),
        'timeout' => 30,
    ] );

    $created = is_wp_error( $response ) ? null : json_decode( wp_remote_retrieve_body( $response ), true );
    if ( empty( $created['id'] ) ) {
        error_log( 'wearit_stripe: coupon ' . $code . ' could not be created — ' . ( is_wp_error( $response ) ? $response->get_error_message() : wp_remote_retrieve_body( $response ) ) );
        return false;
    }

    error_log( 'wearit_stripe: coupon ' . $code . ' created in Stripe' );
    set_transient( $flag, 1, DAY_IN_SECONDS );
    return true;
}

/** The order already made for a Checkout Session, or 0. */
function wearit_stripe_order_for_session( $session_id ) {
    $ids = get_posts( [
        'post_type'   => 'wearit_order',
        'post_status' => 'any',
        'meta_key'    => '_wearit_stripe_session',
        'meta_value'  => $session_id,
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    return $ids ? (int) $ids[0] : 0;
}

/**
 * Turn a paid Checkout Session into a wearit_order. Returns the order ID, or 0
 * when the session isn't paid (yet) or something failed.
 *
 * Called from both the webhook and the thank-you page, which usually arrive at the
 * same moment, so it is safe to call twice: an order is made once per session.
 * Everything is read back from Stripe, never from the request that triggered it.
 */
function wearit_stripe_fulfill( $session_id ) {
    if ( ! preg_match( '/^cs_(test|live)_[A-Za-z0-9]+$/', (string) $session_id ) ) {
        return 0;
    }

    $order_id = wearit_stripe_order_for_session( $session_id );
    if ( $order_id ) {
        return $order_id;
    }

    $session = wearit_stripe_get( 'checkout/sessions/' . $session_id );
    if ( ! $session || ( $session['payment_status'] ?? '' ) === 'unpaid' ) {
        return 0; // not paid, or a delayed method still pending — async_payment_succeeded comes later
    }

    // One request at a time per session. add_option fails if the row exists, so it works as a lock.
    // A lock older than a minute is from a request that died, so it is taken over.
    $lock = 'wearit_stripe_lock_' . $session_id;
    if ( ! add_option( $lock, time(), '', false ) ) {
        if ( time() - (int) get_option( $lock ) < 60 ) {
            for ( $i = 0; $i < 10; $i++ ) { // the other request is creating it; wait up to 5 s
                usleep( 500000 );
                if ( $order_id = wearit_stripe_order_for_session( $session_id ) ) {
                    return $order_id;
                }
            }
            return 0;
        }
        update_option( $lock, time(), false );
    }

    $lines = wearit_stripe_get( 'checkout/sessions/' . $session_id . '/line_items', [
        'limit'  => 100,
        'expand' => [ 'data.price.product' ],
    ] );
    if ( ! $lines ) {
        delete_option( $lock );
        return 0;
    }

    // Stripe amounts are cents; orders are stored in euros like everything else on the site.
    $items = [];
    foreach ( $lines['data'] as $line ) {
        $qty     = (int) $line['quantity'];
        $items[] = [
            'id'         => $line['price']['product']['metadata']['cart_key'] ?? '',
            'title'      => $line['description'] ?? '',
            'sku'        => '',
            'size'       => '',
            'qty'        => $qty,
            'price'      => $qty ? $line['amount_subtotal'] / 100 / $qty : 0,
            'line_total' => $line['amount_subtotal'] / 100,
        ];
    }

    $details  = $session['customer_details'] ?? [];
    $shipping = $session['collected_information']['shipping_details'] ?? [];
    $address  = $shipping['address'] ?? ( $details['address'] ?? [] );

    $customer = [
        'name'     => sanitize_text_field( $shipping['name'] ?? ( $details['individual_name'] ?? ( $details['name'] ?? '' ) ) ),
        'email'    => sanitize_email( $details['email'] ?? '' ),
        'address'  => sanitize_text_field( trim( ( $address['line1'] ?? '' ) . ' ' . ( $address['line2'] ?? '' ) ) ),
        'city'     => sanitize_text_field( $address['city'] ?? '' ),
        'postcode' => sanitize_text_field( $address['postal_code'] ?? '' ),
        'country'  => sanitize_text_field( $address['country'] ?? '' ),
        'notes'    => '',
        'payment'  => 'card',
    ];

    $order_id = wearit_create_order( $customer, $items, [
        'subtotal' => $session['amount_subtotal'] / 100,
        'shipping' => ( $session['total_details']['amount_shipping'] ?? 0 ) / 100,
        'fee'      => 0,
        'discount' => ( $session['total_details']['amount_discount'] ?? 0 ) / 100,
        'coupon'   => $session['metadata']['coupon'] ?? '',
        'total'    => $session['amount_total'] / 100,
    ] );

    if ( ! $order_id ) {
        error_log( 'wearit_stripe: order could not be created for ' . $session_id );
        delete_option( $lock );
        return 0;
    }

    $coupon  = $session['metadata']['coupon'] ?? '';
    $user_id = (int) ( $session['metadata']['user_id'] ?? 0 );
    if ( $coupon && $user_id ) {
        wearit_coupon_mark_used( $coupon, $user_id );
    }

    update_post_meta( $order_id, '_wearit_stripe_session', $session_id );
    update_post_meta( $order_id, '_wearit_stripe_payment_intent', $session['payment_intent'] ?? '' );
    update_post_meta( $order_id, '_wearit_key', wp_hash( 'wearit_order_' . $order_id ) ); // same key as the form handler sets
    wearit_set_payment_status( $order_id, 'paid' ); // also moves it to Preparing, see orders.php
    delete_option( $lock );

    error_log( 'wearit_stripe: order ' . $order_id . ' created for ' . $session_id );
    return $order_id;
}

/**
 * POST /wp-json/wearit/v1/stripe/webhook
 * Stripe calls this after a payment. Only signed events are accepted.
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'wearit/v1', '/stripe/webhook', [
        'methods'             => 'POST',
        'callback'            => 'wearit_rest_stripe_webhook',
        'permission_callback' => '__return_true', // the signature check below is the permission
    ] );
} );

function wearit_rest_stripe_webhook( WP_REST_Request $request ) {
    $secret = wearit_stripe_key( 'STRIPE_WEBHOOK_SECRET' );
    if ( $secret === '' ) {
        error_log( 'wearit_stripe: webhook received but STRIPE_WEBHOOK_SECRET is not set' );
        return new WP_Error( 'wearit_stripe_webhook_secret', 'Webhook secret not set.', [ 'status' => 500 ] );
    }

    $payload = $request->get_body();
    if ( ! wearit_stripe_signature_ok( $payload, (string) $request->get_header( 'stripe-signature' ), $secret ) ) {
        error_log( 'wearit_stripe: webhook signature check failed' );
        return new WP_Error( 'wearit_stripe_signature', 'Invalid signature.', [ 'status' => 400 ] );
    }

    $event = json_decode( $payload, true );
    $type  = $event['type'] ?? '';

    // completed: paid right away (cards). async_payment_succeeded: a delayed method cleared later.
    if ( in_array( $type, [ 'checkout.session.completed', 'checkout.session.async_payment_succeeded' ], true ) ) {
        $session = $event['data']['object'] ?? [];
        if ( ( $session['payment_status'] ?? '' ) !== 'unpaid' && ! wearit_stripe_fulfill( $session['id'] ?? '' ) ) {
            // A 500 makes Stripe retry later.
            return new WP_Error( 'wearit_stripe_fulfill', 'Order could not be created.', [ 'status' => 500 ] );
        }
    }

    return rest_ensure_response( [ 'received' => true ] );
}

/**
 * Check the Stripe-Signature header: "t=<timestamp>,v1=<hmac>[,v1=…]".
 * The HMAC is SHA-256 of "<timestamp>.<raw body>" with the webhook secret.
 * Events older than 5 minutes are refused, so a captured one can't be replayed.
 */
function wearit_stripe_signature_ok( $payload, $header, $secret, $tolerance = 300 ) {
    $timestamp  = 0;
    $signatures = [];
    foreach ( explode( ',', $header ) as $part ) {
        [ $key, $value ] = array_pad( explode( '=', trim( $part ), 2 ), 2, '' );
        if ( $key === 't' ) {
            $timestamp = (int) $value;
        } elseif ( $key === 'v1' ) {
            $signatures[] = $value;
        }
    }
    if ( ! $timestamp || ! $signatures || abs( time() - $timestamp ) > $tolerance ) {
        return false;
    }

    $expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
    foreach ( $signatures as $signature ) {
        if ( hash_equals( $expected, $signature ) ) {
            return true;
        }
    }
    return false;
}

/**
 * Stripe.js (loaded from js.stripe.com, never bundled) and the script that mounts
 * the form — only on the checkout page, and only when card payments are on.
 */
add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_page_template( 'checkoutPage.php' ) || ! wearit_stripe_is_configured() ) {
        return;
    }

    wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/dahlia/stripe.js', [], null, false );
    wp_enqueue_script(
        'wearit-checkout-stripe',
        get_template_directory_uri() . '/assets/js/checkout-stripe.js',
        [ 'stripe-js' ],
        '1.0.2',
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );
    wp_localize_script( 'wearit-checkout-stripe', 'wearitStripe', [
        'publishableKey' => wearit_stripe_key( 'STRIPE_PUBLISHABLE_KEY' ),
        'sessionUrl'     => rest_url( 'wearit/v1/checkout/session' ),
        'nonce'          => wp_create_nonce( 'wp_rest' ),
    ] );
} );

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
 * Back from Stripe with ?session_id=: make (or find) the order, empty the cart,
 * then continue as the order form does — redirect to ?order=&key=.
 * Runs before the template, because clearing the cart cookie needs the headers.
 */
add_action( 'template_redirect', function () {
    if ( ! isset( $_GET['session_id'] ) || ! is_page_template( 'thankYouPage.php' ) || ! wearit_stripe_is_configured() ) {
        return;
    }

    $order_id = wearit_stripe_fulfill( sanitize_text_field( wp_unslash( $_GET['session_id'] ) ) );
    if ( ! $order_id ) {
        return; // not paid (yet): the page shows its "order not found" text
    }

    wearit_cart_save_raw( [] );
    wp_safe_redirect( add_query_arg( [
        'order' => $order_id,
        'key'   => get_post_meta( $order_id, '_wearit_key', true ),
    ], wearit_thankyou_url() ) );
    exit;
} );

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