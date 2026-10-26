<?php
/**
 * Cart — stored in a cookie as { key: quantity }.
 *
 * A key is either
 *   "236"              a product post ID (shop page, wearit_get_product()), or
 *   "drop-2-1a2b3c4d"  row 2 of the front page "drops_items" repeater; the last
 *                      part is a hash of the row's title, so if the rows are
 *                      edited or reordered an old cart entry is dropped instead
 *                      of turning into a different item.
 *
 * Only keys and quantities live in the browser. Titles, prices and images are
 * always read on the server, so an edited cookie can never change a price.
 *
 * REST endpoints (used by assets/js/cart.js):
 *   POST /wp-json/wearit/v1/cart/add     { key }
 *   POST /wp-json/wearit/v1/cart/remove  { key }
 *   POST /wp-json/wearit/v1/cart/update  { key, qty }   qty 0 removes the item
 * All return { count, html } where html is the fresh mini-cart panel.
 * Send page: true (the cart page does) to also get page_html, the fresh
 * cart page body (template-parts/cart-page.php).
 */

const WEARIT_CART_COOKIE = 'wearit_cart';
const WEARIT_CART_MAX_QTY = 99;

// ── Cart keys ─────────────────────────────────────────────────

function wearit_cart_is_valid_key( $key ) {
    return (bool) preg_match( '/^(\d+|drop-\d+-[a-f0-9]{8})$/', (string) $key );
}

/**
 * Cart key for one row of the front page "Drops" section.
 * $index is get_row_index() (1-based).
 */
function wearit_drop_cart_key( $index, $title ) {
    return 'drop-' . (int) $index . '-' . substr( md5( (string) $title ), 0, 8 );
}

/**
 * The rows of the front page "Drops" section.
 */
function wearit_get_drops() {
    static $drops = null;
    if ( $drops !== null ) {
        return $drops;
    }

    $front = (int) get_option( 'page_on_front' );
    $rows  = ( $front && function_exists( 'get_field' ) ) ? get_field( 'drops_items', $front ) : null;
    $drops = is_array( $rows ) ? array_values( $rows ) : [];
    return $drops;
}

/**
 * Everything the cart needs about one key, or null if it no longer exists
 * or cannot be bought.
 *
 * @return array|null [ 'key', 'title', 'permalink', 'image', 'meta', 'price' (float) ]
 */
function wearit_cart_resolve( $key ) {
    $key = (string) $key;

    // Product from the shop
    if ( ctype_digit( $key ) ) {
        $product = wearit_get_product( (int) $key );
        if ( ! $product || ! $product['in_stock'] || get_post_status( $product['id'] ) !== 'publish' ) {
            return null;
        }
        return [
            'key'       => $key,
            'title'     => $product['title'],
            'permalink' => $product['permalink'],
            'image'     => $product['image'],
            'meta'      => $product['color_label'],
            'price'     => $product['price'],
        ];
    }

    // Row from the front page "Drops" section
    if ( preg_match( '/^drop-(\d+)-[a-f0-9]{8}$/', $key, $m ) ) {
        $index = (int) $m[1];
        $row   = wearit_get_drops()[ $index - 1 ] ?? null;
        $title = $row['drops_title'] ?? '';
        if ( ! $row || $title === '' || wearit_drop_cart_key( $index, $title ) !== $key ) {
            return null;
        }
        // "$49.99" → 49.99
        $price = (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '.', (string) ( $row['drops_price'] ?? '' ) ) );
        if ( $price <= 0 ) {
            return null;
        }
        $image = $row['drops_image'] ?? null;
        return [
            'key'       => $key,
            'title'     => $title,
            'permalink' => home_url( '/#drops' ),
            'image'     => is_array( $image ) ? ( $image['sizes']['thumbnail'] ?? $image['url'] ) : '',
            'meta'      => (string) ( $row['drops_description'] ?? '' ),
            'price'     => $price,
        ];
    }

    return null;
}

// ── Reading / writing the cookie ──────────────────────────────

/**
 * The raw cart: [ key (string) => quantity (int) ], cleaned.
 */
function wearit_cart_get_raw() {
    global $wearit_cart_raw;
    if ( is_array( $wearit_cart_raw ) ) {
        return $wearit_cart_raw;
    }

    $raw  = isset( $_COOKIE[ WEARIT_CART_COOKIE ] ) ? wp_unslash( $_COOKIE[ WEARIT_CART_COOKIE ] ) : '';
    $data = json_decode( (string) $raw, true );

    $cart = [];
    if ( is_array( $data ) ) {
        foreach ( $data as $key => $qty ) {
            $key = (string) $key;
            $qty = min( absint( $qty ), WEARIT_CART_MAX_QTY );
            if ( wearit_cart_is_valid_key( $key ) && $qty ) {
                $cart[ $key ] = $qty;
            }
        }
    }

    $wearit_cart_raw = $cart;
    return $cart;
}

/**
 * Save the raw cart to the cookie (30 days) and to this request.
 */
function wearit_cart_save_raw( array $cart ) {
    global $wearit_cart_raw;
    $wearit_cart_raw = $cart;

    $value = $cart ? wp_json_encode( (object) $cart ) : '';
    setcookie( WEARIT_CART_COOKIE, $value, [
        'expires'  => $cart ? time() + 30 * DAY_IN_SECONDS : time() - HOUR_IN_SECONDS,
        'path'     => COOKIEPATH ? COOKIEPATH : '/',
        'domain'   => COOKIE_DOMAIN,
        'secure'   => is_ssl(),
        'httponly' => true,
        'samesite' => 'Lax',
    ] );
}

// ── The cart as the templates use it ──────────────────────────

/**
 * Everything the mini-cart needs.
 *
 * @return array [
 *   'items' => [ wearit_cart_resolve() array + 'qty' => int, 'line_total' => float, … ],
 *   'count' => int   (total quantity),
 *   'subtotal' => float,
 *   'subtotal_formatted' => string,
 * ]
 */
function wearit_get_cart() {
    $items    = [];
    $count    = 0;
    $subtotal = 0.0;

    foreach ( wearit_cart_get_raw() as $key => $qty ) {
        // Skips products that were unpublished and drops that were edited since they were added.
        $item = wearit_cart_resolve( $key );
        if ( ! $item ) {
            continue;
        }
        $item['qty']        = $qty;
        $item['line_total'] = $item['price'] * $qty;
        $items[]   = $item;
        $count    += $qty;
        $subtotal += $item['line_total'];
    }

    return [
        'items'              => $items,
        'count'              => $count,
        'subtotal'           => $subtotal,
        'subtotal_formatted' => wearit_format_price( $subtotal ),
    ];
}

// ── Cart page (cartPage.php) ──────────────────────────────────

/**
 * ID of the full cart page: the page using the "Cart Page" template,
 * or else the page with slug "cart". 0 when neither exists.
 * Also works in REST requests, where there is no current page.
 */
function wearit_cart_page_id() {
    static $id = null;
    if ( $id !== null ) {
        return $id;
    }

    if ( is_page_template( 'cartPage.php' ) ) {
        $id = get_queried_object_id();
        return $id;
    }

    $pages = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'publish',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'cartPage.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    if ( $pages ) {
        $id = (int) $pages[0];
        return $id;
    }

    $page = get_page_by_path( 'cart' );
    $id   = $page ? (int) $page->ID : 0;
    return $id;
}

/**
 * URL of the full cart page.
 */
function wearit_cart_url() {
    $id = wearit_cart_page_id();
    return $id ? get_permalink( $id ) : home_url( '/cart/' );
}

/**
 * The cart page's text fields, per admin tab: name => [ label, fallback, field type ].
 * The fallback (the text from the design) is shown while a field is empty.
 */
function wearit_cart_page_texts() {
    return [
        'Hero' => [
            'cart_eyebrow'        => [ 'Eyebrow', 'Your Selection', 'text' ],
            'cart_heading'        => [ 'Heading', 'Your', 'text' ],
            'cart_heading_accent' => [ 'Heading (lime part)', 'Cart', 'text' ],
        ],
        'Empty cart' => [
            'cart_empty_heading' => [ 'Heading', 'Nothing here', 'text' ],
            'cart_empty_accent'  => [ 'Heading (lime part)', 'yet.', 'text' ],
            'cart_empty_text'    => [ 'Text', 'Your next uniform is waiting. Explore the current drop and add a piece to get started.', 'textarea' ],
            'cart_empty_button'  => [ 'Button label', 'Shop Products', 'text' ],
        ],
        'Order summary' => [
            'cart_summary_title'  => [ 'Title', 'Order Summary', 'text' ],
            'cart_checkout_label' => [ 'Checkout button', 'Go to Checkout', 'text' ],
            'cart_back_label'     => [ 'Back button', 'Back to Shop More', 'text' ],
            'cart_tax_note'       => [ 'Note under the buttons', 'Taxes calculated at checkout', 'text' ],
        ],
    ];
}

/**
 * One SCF field of the cart page. When it is empty: $fallback if given,
 * otherwise the fallback from wearit_cart_page_texts().
 */
function wearit_cart_text( $name, $fallback = null ) {
    $page  = wearit_cart_page_id();
    $value = ( $page && function_exists( 'get_field' ) ) ? get_field( $name, $page ) : '';
    if ( $value !== null && $value !== false && $value !== '' ) {
        return $value;
    }

    if ( $fallback === null ) {
        foreach ( wearit_cart_page_texts() as $group ) {
            if ( isset( $group[ $name ] ) ) {
                return $group[ $name ][1];
            }
        }
        return '';
    }
    return $fallback;
}

/**
 * Cart page prices always show cents, as in the design: 89 → "$89.00".
 */
function wearit_cart_format_price( $price ) {
    return '$' . number_format( (float) $price, 2 );
}

/**
 * Shipping and total for the order summary.
 * Shipping is free when the cart is empty, when no shipping price is set,
 * or when the subtotal reaches the "Free shipping from" amount.
 *
 * @return array [ 'shipping' => float, 'total' => float ]
 */
function wearit_cart_totals( array $cart ) {
    $price     = (float) wearit_cart_text( 'cart_shipping_price', 0 );
    $free_from = (float) wearit_cart_text( 'cart_free_shipping_from', 0 );

    $is_free  = ! $cart['items'] || $price <= 0 || ( $free_from > 0 && $cart['subtotal'] >= $free_from );
    $shipping = $is_free ? 0.0 : $price;

    return [
        'shipping' => $shipping,
        'total'    => $cart['subtotal'] + $shipping,
    ];
}

// ── Cart page fields (registered in code so every teammate gets them via git) ──

add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $fields = [];
    foreach ( wearit_cart_page_texts() as $tab => $group ) {
        $fields[] = [
            'key'   => 'field_wearit_cart_tab_' . sanitize_key( $tab ),
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

    $fields[] = [
        'key'          => 'field_wearit_cart_shipping_price',
        'label'        => 'Shipping price',
        'name'         => 'cart_shipping_price',
        'type'         => 'number',
        'instructions' => 'Flat shipping price in US dollars. Leave empty or 0 for free shipping on every order.',
        'min'          => 0,
        'step'         => 0.01,
        'prepend'      => '$',
    ];
    $fields[] = [
        'key'          => 'field_wearit_cart_free_shipping_from',
        'label'        => 'Free shipping from',
        'name'         => 'cart_free_shipping_from',
        'type'         => 'number',
        'instructions' => 'Shipping is free when the subtotal reaches this amount. Leave empty to never make it free.',
        'min'          => 0,
        'step'         => 0.01,
        'prepend'      => '$',
    ];

    acf_add_local_field_group( [
        'key'      => 'group_wearit_cart_page',
        'title'    => 'Cart page',
        'fields'   => $fields,
        'location' => [
            [ [ 'param' => 'page_template', 'operator' => '==', 'value' => 'cartPage.php' ] ],
        ],
        'position'       => 'normal',
        'menu_order'     => 0,
        'hide_on_screen' => [ 'the_content' ],
    ] );
} );

// ── REST endpoints ────────────────────────────────────────────

add_action( 'rest_api_init', function () {
    $args = [
        'key' => [
            'required'          => true,
            'type'              => 'string',
            'validate_callback' => 'wearit_cart_is_valid_key',
        ],
        // true from the cart page: the response then also has page_html
        'page' => [
            'type'    => 'boolean',
            'default' => false,
        ],
    ];

    register_rest_route( 'wearit/v1', '/cart/add', [
        'methods'             => 'POST',
        'callback'            => 'wearit_rest_cart_add',
        'permission_callback' => '__return_true',
        'args'                => $args,
    ] );

    register_rest_route( 'wearit/v1', '/cart/remove', [
        'methods'             => 'POST',
        'callback'            => 'wearit_rest_cart_remove',
        'permission_callback' => '__return_true',
        'args'                => $args,
    ] );

    register_rest_route( 'wearit/v1', '/cart/update', [
        'methods'             => 'POST',
        'callback'            => 'wearit_rest_cart_update',
        'permission_callback' => '__return_true',
        'args'                => $args + [
            'qty' => [
                'required' => true,
                'type'     => 'integer',
                'minimum'  => 0,
            ],
        ],
    ] );
} );

function wearit_rest_cart_add( WP_REST_Request $request ) {
    $key = (string) $request['key'];

    if ( ! wearit_cart_resolve( $key ) ) {
        return new WP_Error( 'wearit_cart_unavailable', 'This item is not available.', [ 'status' => 404 ] );
    }

    $cart         = wearit_cart_get_raw();
    $cart[ $key ] = min( ( $cart[ $key ] ?? 0 ) + 1, WEARIT_CART_MAX_QTY );
    wearit_cart_save_raw( $cart );

    return wearit_rest_cart_response( $request );
}

function wearit_rest_cart_remove( WP_REST_Request $request ) {
    $cart = wearit_cart_get_raw();
    unset( $cart[ (string) $request['key'] ] );
    wearit_cart_save_raw( $cart );

    return wearit_rest_cart_response( $request );
}

/**
 * Set the quantity of an item already in the cart (the − / + buttons on the cart page).
 */
function wearit_rest_cart_update( WP_REST_Request $request ) {
    $key  = (string) $request['key'];
    $qty  = min( (int) $request['qty'], WEARIT_CART_MAX_QTY );
    $cart = wearit_cart_get_raw();

    if ( $qty === 0 || ! wearit_cart_resolve( $key ) ) {
        unset( $cart[ $key ] );
    } elseif ( isset( $cart[ $key ] ) ) {
        $cart[ $key ] = $qty;
    }
    wearit_cart_save_raw( $cart );

    return wearit_rest_cart_response( $request );
}

function wearit_rest_cart_response( WP_REST_Request $request ) {
    ob_start();
    get_template_part( 'template-parts/mini-cart' );
    $data = [
        'count' => wearit_get_cart()['count'],
        'html'  => ob_get_clean(),
    ];

    if ( $request['page'] ) {
        ob_start();
        get_template_part( 'template-parts/cart-page' );
        $data['page_html'] = ob_get_clean();
    }

    return rest_ensure_response( $data );
}

// ── Script ────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'wearit-cart',
        get_template_directory_uri() . '/assets/js/cart.js',
        [],
        '1.2.0',
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );
    wp_localize_script( 'wearit-cart', 'wearitCart', [
        'addUrl'    => rest_url( 'wearit/v1/cart/add' ),
        'removeUrl' => rest_url( 'wearit/v1/cart/remove' ),
        'updateUrl' => rest_url( 'wearit/v1/cart/update' ),
    ] );
} );
