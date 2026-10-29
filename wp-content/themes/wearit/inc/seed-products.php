<?php
/**
 * Demo products: creates the 5 categories and 10 products.
 *
 * Safe to run more than once — anything that already exists (matched by slug)
 * is skipped, so it never overwrites edits made in the admin.
 *
 * Run it from the admin:  Products → Seed demo products → click the button.
 * Images: every product uses the file assets/products/{slug}.webp (or .jpg/.png)
 * automatically (see wearit_get_product_image()); a "Product image" or featured image set in the admin wins.
 */

function wearit_seed_categories() {
    return [
        'jackets'     => 'Jackets',
        'hoodies'     => 'Hoodies',
        't-shirts'    => 'T-Shirts',
        'pants-jeans' => 'Pants & Jeans',
        'sneakers'    => 'Sneakers',
    ];
}

/**
 * Sizes from $from to $to (inclusive) in the canonical order, e.g. ('S', 'XXL').
 */
function wearit_size_range( $from, $to ) {
    $all   = wearit_product_size_list();
    $start = array_search( $from, $all, true );
    $end   = array_search( $to, $all, true );
    return array_slice( $all, $start, $end - $start + 1 );
}

function wearit_seed_product_list() {
    return [
        // ── The six products from the Figma design (in design order) ──
        [
            'slug' => 'shadow-hoodie', 'title' => 'Shadow Hoodie', 'category' => 'hoodies',
            'color' => 'black', 'price' => 89, 'sizes' => wearit_size_range( 'S', 'XXL' ),
            'is_new' => 1, 'sku' => 'WI-HOOD-001',
            'text' => 'Heavyweight brushed-fleece hoodie with a boxy fit and an oversized kangaroo pocket.',
        ],
        [
            'slug' => 'utility-cargo', 'title' => 'Utility Cargo', 'category' => 'pants-jeans',
            'color' => 'olive', 'price' => 129, 'sizes' => wearit_size_range( '28', '38' ),
            'is_new' => 0, 'sku' => 'WI-PANT-001',
            'text' => 'Relaxed cargo pants in washed cotton twill with deep utility pockets.',
        ],
        [
            'slug' => 'clean-force-1', 'title' => 'Clean Force 1', 'category' => 'sneakers',
            'color' => 'bone', 'price' => 149, 'sizes' => wearit_size_range( 'US 6', 'US 13' ),
            'is_new' => 0, 'sku' => 'WI-SNKR-001',
            'text' => 'Low-top leather sneaker with a chunky cupsole. Clean, simple, goes with everything.',
        ],
        [
            'slug' => 'night-shift-jacket', 'title' => 'Night Shift Jacket', 'category' => 'jackets',
            'color' => 'black', 'price' => 219, 'sizes' => wearit_size_range( 'S', 'XXL' ),
            'is_new' => 1, 'sku' => 'WI-JCKT-001',
            'text' => 'Water-resistant shell jacket with colour-blocked panels and a packable hood.',
        ],
        [
            'slug' => 'raw-denim-01', 'title' => 'Raw Denim 01', 'category' => 'pants-jeans',
            'color' => 'slate', 'price' => 139, 'sizes' => wearit_size_range( '28', '38' ),
            'is_new' => 0, 'sku' => 'WI-PANT-002',
            'text' => 'Straight-leg jeans in 13oz selvedge denim. Made to fade your way.',
        ],
        [
            'slug' => 'heavy-leather-bomber', 'title' => 'Heavy Leather Bomber', 'category' => 'jackets',
            'color' => 'black', 'price' => 289, 'sizes' => wearit_size_range( 'S', 'XL' ),
            'is_new' => 0, 'sku' => 'WI-JCKT-002',
            'text' => 'Full-grain leather biker bomber with an asymmetric zip and quilted lining.',
        ],

        // ── Four extra products so every category has two ──
        [
            'slug' => 'core-heavy-tee', 'title' => 'Core Heavy Tee', 'category' => 't-shirts',
            'color' => 'bone', 'price' => 39, 'sizes' => wearit_size_range( 'S', 'XXL' ),
            'is_new' => 0, 'sku' => 'WI-TEES-001',
            'text' => '260gsm heavy cotton tee with a boxy cut and a ribbed crew neck.',
        ],
        [
            'slug' => 'volt-logo-tee', 'title' => 'Volt Logo Tee', 'category' => 't-shirts',
            'color' => 'acid', 'price' => 45, 'sizes' => wearit_size_range( 'S', 'XL' ),
            'is_new' => 1, 'sku' => 'WI-TEES-002',
            'text' => 'Acid-coloured tee with the WearIt wordmark across the chest.',
        ],
        [
            'slug' => 'slate-zip-hoodie', 'title' => 'Slate Zip Hoodie', 'category' => 'hoodies',
            'color' => 'slate', 'price' => 99, 'sizes' => wearit_size_range( 'S', 'XXL' ),
            'is_new' => 0, 'sku' => 'WI-HOOD-002',
            'text' => 'Oversized hoodie in garment-dyed slate fleece with a chest print and a big front pocket.',
        ],
        [
            'slug' => 'night-runner', 'title' => 'Night Runner', 'category' => 'sneakers',
            'color' => 'bone', 'price' => 159, 'sizes' => wearit_size_range( 'US 7', 'US 13' ),
            'is_new' => 0, 'sku' => 'WI-SNKR-002',
            'text' => 'Chunky bone-coloured runner in suede and mesh with a cushioned sole.',
        ],
    ];
}

/**
 * Create whatever is missing.
 *
 * @return string[] Human-readable log, one line per thing done or skipped.
 */
function wearit_seed_products() {
    $log = [];

    foreach ( wearit_seed_categories() as $slug => $name ) {
        if ( term_exists( $slug, 'product_category' ) ) {
            $log[] = "Category “{$name}” already exists — skipped.";
            continue;
        }
        $result = wp_insert_term( $name, 'product_category', [ 'slug' => $slug ] );
        $log[]  = is_wp_error( $result )
            ? "Category “{$name}” FAILED: " . $result->get_error_message()
            : "Category “{$name}” created.";
    }

    foreach ( wearit_seed_product_list() as $order => $item ) {
        if ( get_page_by_path( $item['slug'], OBJECT, 'product' ) ) {
            $log[] = "Product “{$item['title']}” already exists — skipped.";
            continue;
        }

        $post_id = wp_insert_post( [
            'post_type'    => 'product',
            'post_status'  => 'publish',
            'post_title'   => $item['title'],
            'post_name'    => $item['slug'],
            'post_excerpt' => $item['text'],
            'menu_order'   => $order + 1,
        ], true );

        if ( is_wp_error( $post_id ) ) {
            $log[] = "Product “{$item['title']}” FAILED: " . $post_id->get_error_message();
            continue;
        }

        wp_set_object_terms( $post_id, $item['category'], 'product_category' );

        $fields = [
            'price'    => $item['price'],
            'color'    => $item['color'],
            'sizes'    => $item['sizes'],
            'is_new'   => $item['is_new'],
            'in_stock' => 1,
            'sku'      => $item['sku'],
        ];
        // Field keys (not names) so SCF also stores its internal field references.
        $keys = [
            'price'    => 'field_wearit_price',
            'color'    => 'field_wearit_color',
            'sizes'    => 'field_wearit_sizes',
            'is_new'   => 'field_wearit_is_new',
            'in_stock' => 'field_wearit_in_stock',
            'sku'      => 'field_wearit_sku',
        ];
        foreach ( $fields as $name => $value ) {
            if ( function_exists( 'update_field' ) ) {
                update_field( $keys[ $name ], $value, $post_id );
            } else {
                update_post_meta( $post_id, $name, $value );
            }
        }

        $log[] = "Product “{$item['title']}” created (ID {$post_id}).";
    }

    return $log;
}

// ── Admin button: Products → Seed demo products ───────────────

add_action( 'admin_menu', function () {
    add_submenu_page(
        'edit.php?post_type=product',
        'Seed demo products',
        'Seed demo products',
        'manage_options',
        'wearit-seed-products',
        'wearit_render_seed_page'
    );
} );

function wearit_render_seed_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    echo '<div class="wrap"><h1>Seed demo products</h1>';
    echo '<p>Creates the 5 categories and 10 products used on the shop page. Anything that already exists is skipped, so it is safe to click more than once.</p>';
    echo '<p><strong>Note:</strong> this site uses the shared team database, so the products appear for everyone.</p>';

    if ( isset( $_POST['wearit_seed'] ) && check_admin_referer( 'wearit_seed_products' ) ) {
        echo '<h2>Result</h2><ul style="list-style:disc;padding-left:1.5em">';
        foreach ( wearit_seed_products() as $line ) {
            echo '<li>' . esc_html( $line ) . '</li>';
        }
        echo '</ul>';
    }

    echo '<form method="post">';
    wp_nonce_field( 'wearit_seed_products' );
    submit_button( 'Create demo products', 'primary', 'wearit_seed' );
    echo '</form></div>';
}
