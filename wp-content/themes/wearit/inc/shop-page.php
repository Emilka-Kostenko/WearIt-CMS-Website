<?php
/**
 * Shop page (/products/) — all the text on the page, edited in the admin:
 *   Pages → Products (template "Products Page"), SCF field group "Products Page".
 *
 * /products/ itself is the product archive (archive-product.php), which WordPress serves
 * before any page with the same address. The Products page is only where the text is
 * edited; "View page" still opens the real shop. productsPage.php sends any other
 * address of that page to the shop.
 * Danish: each text field gets a "— Dansk" twin automatically (inc/i18n.php).
 */

/** ID of the page using the "Products Page" template, or 0 when there is none. */
function wearit_shop_page_id() {
    static $id = null;
    if ( $id === null ) {
        $pages = get_posts( [
            'post_type'   => 'page',
            'post_status' => 'publish',
            'numberposts' => 1,
            'fields'      => 'ids',
            'meta_key'    => '_wp_page_template',
            'meta_value'  => 'productsPage.php',
        ] );
        $id = $pages ? (int) $pages[0] : 0;
    }
    return $id;
}

/**
 * All the text on the shop page, with the design's text as fallback for empty fields.
 *
 * @return array eyebrow, heading, heading_accent, filters_title, clear_label,
 *               categories_title, colors_title, count_one, count_many (with {count}),
 *               empty_title, empty_button, badge_new, sizes_label, add_label, sold_out_label
 */
function wearit_get_shop_page() {
    static $shop = [];
    $lang = function_exists( 'wearit_lang' ) ? wearit_lang() : 'en';
    if ( isset( $shop[ $lang ] ) ) {
        return $shop[ $lang ];
    }

    $page_id = wearit_shop_page_id();
    $get = function ( $name, $default ) use ( $page_id ) {
        if ( ! $page_id ) {
            return $default;
        }
        $value = function_exists( 'get_field' ) ? get_field( 'shop_' . $name, $page_id ) : get_post_meta( $page_id, 'shop_' . $name, true );
        return ( $value === null || $value === '' || $value === false ) ? $default : (string) $value;
    };

    $shop[ $lang ] = [
        'eyebrow'          => $get( 'eyebrow', __( 'Available now', 'wearit' ) ),
        'heading'          => $get( 'heading', __( 'All', 'wearit' ) ),
        'heading_accent'   => $get( 'heading_accent', __( 'Products', 'wearit' ) ),
        'filters_title'    => $get( 'filters_title', __( 'Filters', 'wearit' ) ),
        'clear_label'      => $get( 'clear_label', __( 'Clear filters', 'wearit' ) ),
        'categories_title' => $get( 'categories_title', __( 'Categories', 'wearit' ) ),
        'colors_title'     => $get( 'colors_title', __( 'Color', 'wearit' ) ),
        'count_one'        => $get( 'count_one', str_replace( '%d', '{count}', __( 'Showing %d product', 'wearit' ) ) ),
        'count_many'       => $get( 'count_many', str_replace( '%d', '{count}', __( 'Showing %d products', 'wearit' ) ) ),
        'empty_title'      => $get( 'empty_title', __( 'No products match these filters', 'wearit' ) ),
        'empty_button'     => $get( 'empty_button', __( 'Clear filters', 'wearit' ) ),
        'badge_new'        => $get( 'badge_new', __( 'New', 'wearit' ) ),
        'sizes_label'      => $get( 'sizes_label', trim( str_replace( '%s', '', __( 'Sizes %s', 'wearit' ) ) ) ),
        'add_label'        => $get( 'add_label', __( 'Add to Cart', 'wearit' ) ),
        'sold_out_label'   => $get( 'sold_out_label', __( 'Sold out', 'wearit' ) ),
    ];
    return $shop[ $lang ];
}

/** "Showing 7 products" — the count line, from the fields above. */
function wearit_shop_count_text( $count ) {
    $shop = wearit_get_shop_page();
    $text = (int) $count === 1 ? $shop['count_one'] : $shop['count_many'];
    return str_replace( '{count}', (string) (int) $count, $text );
}

// "View page" on the Products page opens the shop.
add_filter( 'page_link', function ( $link, $post_id ) {
    return (int) $post_id === wearit_shop_page_id() ? get_post_type_archive_link( 'product' ) : $link;
}, 10, 2 );
