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
 * Both return { count, html } where html is the fresh mini-cart panel.
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

/**
 * URL of the full cart page: the page with slug "cart" once it exists.
 */
function wearit_cart_url() {
    $page = get_page_by_path( 'cart' );
    return $page ? get_permalink( $page ) : home_url( '/cart/' );
}

// ── REST endpoints ────────────────────────────────────────────

add_action( 'rest_api_init', function () {
    $args = [
        'key' => [
            'required'          => true,
            'type'              => 'string',
            'validate_callback' => 'wearit_cart_is_valid_key',
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
} );

function wearit_rest_cart_add( WP_REST_Request $request ) {
    $key = (string) $request['key'];

    if ( ! wearit_cart_resolve( $key ) ) {
        return new WP_Error( 'wearit_cart_unavailable', 'This item is not available.', [ 'status' => 404 ] );
    }

    $cart         = wearit_cart_get_raw();
    $cart[ $key ] = min( ( $cart[ $key ] ?? 0 ) + 1, WEARIT_CART_MAX_QTY );
    wearit_cart_save_raw( $cart );

    return wearit_rest_cart_response();
}

function wearit_rest_cart_remove( WP_REST_Request $request ) {
    $cart = wearit_cart_get_raw();
    unset( $cart[ (string) $request['key'] ] );
    wearit_cart_save_raw( $cart );

    return wearit_rest_cart_response();
}

function wearit_rest_cart_response() {
    ob_start();
    get_template_part( 'template-parts/mini-cart' );
    $html = ob_get_clean();

    return rest_ensure_response( [
        'count' => wearit_get_cart()['count'],
        'html'  => $html,
    ] );
}

// ── Script ────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'wearit-cart',
        get_template_directory_uri() . '/assets/js/cart.js',
        [],
        '1.1.0',
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );
    wp_localize_script( 'wearit-cart', 'wearitCart', [
        'addUrl'    => rest_url( 'wearit/v1/cart/add' ),
        'removeUrl' => rest_url( 'wearit/v1/cart/remove' ),
    ] );
} );
