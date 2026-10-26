<?php
/**
 * Products — post type, taxonomy, fields, filtering and helpers.
 *
 * DATA CONTRACT FOR CART / CHECKOUT: use wearit_get_product( $post_id ).
 * See PRODUCTS-DATA.md in the theme root for the full description.
 */

// ── Fixed lists (single source of truth) ──────────────────────

/**
 * Product colors: slug => [ label, hex ]. Hex values come from the Figma swatches.
 */
function wearit_product_colors() {
    return [
        'black' => [ 'label' => 'Black', 'hex' => '#1a1a1a' ],
        'bone'  => [ 'label' => 'Bone',  'hex' => '#e8e4d9' ],
        'acid'  => [ 'label' => 'Acid',  'hex' => '#c8ff00' ],
        'olive' => [ 'label' => 'Olive', 'hex' => '#4a5a3a' ],
        'slate' => [ 'label' => 'Slate', 'hex' => '#5b6b7a' ],
        'rust'  => [ 'label' => 'Rust',  'hex' => '#a8532f' ],
    ];
}

/**
 * Every size a product can have, in display order.
 * Clothing: XS–XXL. Bottoms: waist 28–38. Sneakers: "US 6" – "US 13".
 */
function wearit_product_size_list() {
    $sizes = [ 'XS', 'S', 'M', 'L', 'XL', 'XXL', '28', '30', '32', '34', '36', '38' ];
    for ( $n = 6; $n <= 13; $n++ ) {
        $sizes[] = 'US ' . $n;
    }
    return $sizes;
}

/**
 * Same sizes as [ value => label ] for the admin checkbox field.
 * Always use wearit_product_size_list() for ordering/lookups: PHP turns the
 * numeric keys here ('28') into integers, which breaks strict comparisons.
 */
function wearit_product_size_options() {
    $sizes = wearit_product_size_list();
    return array_combine( $sizes, $sizes );
}

// ── Post type + taxonomy ──────────────────────────────────────

add_action( 'init', function () {
    register_post_type( 'product', [
        'labels' => [
            'name'          => 'Products',
            'singular_name' => 'Product',
            'add_new_item'  => 'Add New Product',
            'edit_item'     => 'Edit Product',
            'all_items'     => 'All Products',
            'search_items'  => 'Search Products',
        ],
        'public'       => true,
        'has_archive'  => 'products',
        'rewrite'      => [ 'slug' => 'products', 'with_front' => false ],
        // page-attributes adds the "Order" box: lower number = shown first on the shop page.
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ],
        'show_in_rest' => true,
        'rest_base'    => 'products',
        'menu_icon'    => 'dashicons-tag',
        'menu_position' => 5,
    ] );

    register_taxonomy( 'product_category', 'product', [
        'labels' => [
            'name'          => 'Product Categories',
            'singular_name' => 'Product Category',
            'add_new_item'  => 'Add New Category',
            'all_items'     => 'All Categories',
        ],
        'hierarchical'      => true,
        'public'            => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => [ 'slug' => 'products/category', 'with_front' => false ],
    ] );
} );

// ── Custom fields (registered in code so every teammate gets them via git) ──

add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    $colors = [];
    foreach ( wearit_product_colors() as $slug => $color ) {
        $colors[ $slug ] = $color['label'];
    }

    acf_add_local_field_group( [
        'key'          => 'group_wearit_product',
        'title'        => 'Product details',
        'show_in_rest' => true,
        'fields'       => [
            [
                'key'          => 'field_wearit_price',
                'label'        => 'Price',
                'name'         => 'price',
                'type'         => 'number',
                'instructions' => 'In US dollars, e.g. 89 or 89.90',
                'required'     => 1,
                'min'          => 0,
                'step'         => 0.01,
                'prepend'      => '$',
            ],
            [
                'key'           => 'field_wearit_color',
                'label'         => 'Color',
                'name'          => 'color',
                'type'          => 'select',
                'choices'       => $colors,
                'default_value' => 'black',
                'return_format' => 'value',
                'required'      => 1,
            ],
            [
                'key'           => 'field_wearit_sizes',
                'label'         => 'Sizes',
                'name'          => 'sizes',
                'type'          => 'checkbox',
                'instructions'  => 'Tick every size that is available. Clothing: XS–XXL. Bottoms: 28–38. Sneakers: US 6–13.',
                'choices'       => wearit_product_size_options(),
                'layout'        => 'horizontal',
                'return_format' => 'value',
                'required'      => 1,
            ],
            [
                'key'           => 'field_wearit_is_new',
                'label'         => 'Show "New" badge',
                'name'          => 'is_new',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 0,
            ],
            [
                'key'           => 'field_wearit_in_stock',
                'label'         => 'In stock',
                'name'          => 'in_stock',
                'type'          => 'true_false',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'          => 'field_wearit_sku',
                'label'        => 'SKU',
                'name'         => 'sku',
                'type'         => 'text',
                'instructions' => 'Unique product code, e.g. WI-HOOD-001',
            ],
        ],
        'location' => [
            [ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'product' ] ],
        ],
        'position'       => 'normal',
        'menu_order'     => 0,
        'hide_on_screen' => [],
    ] );
} );

// ── Image size used by the product cards ──────────────────────

add_action( 'after_setup_theme', function () {
    add_image_size( 'product-card', 670, 670, true );
} );

// ── Font weight the product cards need (loaded only on the shop page) ──

add_action( 'wp_enqueue_scripts', function () {
    if ( is_post_type_archive( 'product' ) ) {
        wp_enqueue_style(
            'wearit-products-fonts',
            'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700&display=swap',
            [],
            null
        );
    }
} );

// ── Reading product data ──────────────────────────────────────

/**
 * Read one field. Uses SCF/ACF when it is active, plain post meta otherwise.
 */
function wearit_product_field( $name, $post_id ) {
    if ( function_exists( 'get_field' ) ) {
        return get_field( $name, $post_id );
    }
    $value = get_post_meta( $post_id, $name, true );
    return $value;
}

/**
 * Sort a list of sizes into the canonical display order.
 */
function wearit_sort_sizes( array $sizes ) {
    return array_values( array_intersect( wearit_product_size_list(), $sizes ) );
}

/**
 * "S–XXL", "28–38", "US 6–13" from a list of sizes.
 */
function wearit_format_size_range( array $sizes ) {
    $sizes = wearit_sort_sizes( $sizes );
    if ( empty( $sizes ) ) {
        return '';
    }
    if ( count( $sizes ) === 1 ) {
        return $sizes[0];
    }
    $first = $sizes[0];
    $last  = $sizes[ count( $sizes ) - 1 ];
    // "US 6" + "US 13" → "US 6–13"
    if ( strpos( $first, 'US ' ) === 0 && strpos( $last, 'US ' ) === 0 ) {
        $last = substr( $last, 3 );
    }
    return $first . '–' . $last;
}

/**
 * Price as shown to the customer: "$89" or "$89.90".
 */
function wearit_format_price( $price ) {
    $price = (float) $price;
    $whole = ( floor( $price ) == $price );
    return '$' . number_format( $price, $whole ? 0 : 2 );
}

/**
 * Image for a product: the featured image if one is set, otherwise the
 * file assets/products/{slug}.jpg (or .jpeg/.png/.webp) inside the theme (travels with git),
 * otherwise null (the template shows a placeholder block).
 *
 * @return array|null [ 'url' => string, 'width' => int, 'height' => int ]
 */
function wearit_get_product_image( $post_id, $size = 'product-card' ) {
    if ( has_post_thumbnail( $post_id ) ) {
        $src = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), $size );
        if ( $src ) {
            return [ 'url' => $src[0], 'width' => (int) $src[1], 'height' => (int) $src[2] ];
        }
    }

    $slug = get_post_field( 'post_name', $post_id );
    if ( $slug ) {
        foreach ( [ 'jpg', 'jpeg', 'png', 'webp' ] as $ext ) {
            $file = get_template_directory() . '/assets/products/' . $slug . '.' . $ext;
            if ( file_exists( $file ) ) {
                $dims = getimagesize( $file );
                return [
                    'url'    => get_template_directory_uri() . '/assets/products/' . $slug . '.' . $ext,
                    'width'  => $dims ? (int) $dims[0] : 0,
                    'height' => $dims ? (int) $dims[1] : 0,
                ];
            }
        }
    }

    return null;
}

/**
 * THE product data contract. Everything the cart / checkout needs about one product.
 *
 * @return array|null null if the ID is not a product.
 *   id (int), title, slug, permalink, sku, price (float), price_formatted,
 *   color (slug), color_label, color_hex, sizes (string[]), size_range,
 *   category (slug), category_label, in_stock (bool), is_new (bool),
 *   image (url or '')
 */
function wearit_get_product( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'product' ) {
        return null;
    }

    $colors = wearit_product_colors();
    $slug   = (string) wearit_product_field( 'color', $post->ID );
    $color  = $colors[ $slug ] ?? [ 'label' => '', 'hex' => '#1a1a1a' ];

    $sizes = wearit_product_field( 'sizes', $post->ID );
    $sizes = is_array( $sizes ) ? wearit_sort_sizes( array_map( 'strval', $sizes ) ) : [];

    $terms    = get_the_terms( $post->ID, 'product_category' );
    $category = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0] : null;

    $price = (float) wearit_product_field( 'price', $post->ID );
    $image = wearit_get_product_image( $post->ID );

    return [
        'id'              => (int) $post->ID,
        'title'           => get_the_title( $post ),
        'slug'            => $post->post_name,
        'permalink'       => get_permalink( $post ),
        'sku'             => (string) wearit_product_field( 'sku', $post->ID ),
        'price'           => $price,
        'price_formatted' => wearit_format_price( $price ),
        'color'           => $slug,
        'color_label'     => $color['label'],
        'color_hex'       => $color['hex'],
        'sizes'           => $sizes,
        'size_range'      => wearit_format_size_range( $sizes ),
        'category'        => $category ? $category->slug : '',
        'category_label'  => $category ? $category->name : '',
        'in_stock'        => (bool) wearit_product_field( 'in_stock', $post->ID ),
        'is_new'          => (bool) wearit_product_field( 'is_new', $post->ID ),
        'image'           => $image ? $image['url'] : '',
    ];
}

// ── Shop page filtering (plain PHP, driven by the URL) ────────
//
//   /products/?product_cat=jackets,hoodies&color=black,olive
//
// Several values for one filter are OR'd; the two filters are AND'ed.

/**
 * The filters currently in the URL, cleaned and checked against what exists.
 *
 * @return array [ 'product_cat' => string[], 'color' => string[] ]
 */
function wearit_get_active_filters() {
    static $cache = null;
    if ( $cache !== null ) {
        return $cache;
    }

    $read = function ( $key ) {
        $raw = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : '';
        if ( is_array( $raw ) ) {
            $raw = implode( ',', $raw );
        }
        $parts = array_filter( array_map( 'sanitize_key', explode( ',', (string) $raw ) ) );
        return array_values( array_unique( $parts ) );
    };

    $categories = array_filter(
        $read( 'product_cat' ),
        function ( $slug ) {
            return term_exists( $slug, 'product_category' );
        }
    );
    $colors = array_filter(
        $read( 'color' ),
        function ( $slug ) {
            return isset( wearit_product_colors()[ $slug ] );
        }
    );

    $cache = [
        'product_cat' => array_values( $categories ),
        'color'       => array_values( $colors ),
    ];
    return $cache;
}

/**
 * Color slugs that at least one published product uses. The shop sidebar only
 * shows these swatches, so a color with no products never leads to an empty page.
 *
 * @return string[]
 */
function wearit_colors_in_use() {
    global $wpdb;

    $values = $wpdb->get_col(
        "SELECT DISTINCT pm.meta_value
         FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = 'color'
           AND p.post_type = 'product'
           AND p.post_status = 'publish'"
    );

    return array_values( array_intersect( array_keys( wearit_product_colors() ), $values ) );
}

/**
 * URL of the shop page with one value switched on/off in one filter,
 * keeping everything else that is already selected.
 */
function wearit_filter_url( $key, $value ) {
    $filters = wearit_get_active_filters();
    $current = $filters[ $key ];

    if ( in_array( $value, $current, true ) ) {
        $current = array_values( array_diff( $current, [ $value ] ) );
    } else {
        $current[] = $value;
    }

    $filters[ $key ] = $current;

    $args = [];
    foreach ( $filters as $name => $values ) {
        if ( ! empty( $values ) ) {
            $args[ $name ] = implode( ',', $values );
        }
    }

    $base = get_post_type_archive_link( 'product' );
    // add_query_arg() encodes the commas as %2C; put them back so the address bar stays readable.
    return $args ? str_replace( '%2C', ',', add_query_arg( $args, $base ) ) : $base;
}

function wearit_has_active_filters() {
    $filters = wearit_get_active_filters();
    return ! empty( $filters['product_cat'] ) || ! empty( $filters['color'] );
}

add_action( 'pre_get_posts', function ( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'product' ) ) {
        return;
    }

    $filters = wearit_get_active_filters();

    if ( ! empty( $filters['product_cat'] ) ) {
        $query->set( 'tax_query', [ [
            'taxonomy' => 'product_category',
            'field'    => 'slug',
            'terms'    => $filters['product_cat'],
            'operator' => 'IN',
        ] ] );
    }

    if ( ! empty( $filters['color'] ) ) {
        $query->set( 'meta_query', [ [
            'key'     => 'color',
            'value'   => $filters['color'],
            'compare' => 'IN',
        ] ] );
    }

    // Small catalogue: show everything on one page, in the "Order" set in the admin.
    $query->set( 'posts_per_page', -1 );
    $query->set( 'orderby', [ 'menu_order' => 'ASC', 'date' => 'DESC' ] );
} );
