<?php
/**
 * SEO — <meta name="description"> on every page.
 *
 * Every post, page, product and initiative gets a "Meta description" box in the
 * admin sidebar. When it is empty, a description is built automatically:
 *   product          excerpt + color, sizes and price
 *   post / page      the excerpt, otherwise the start of the content
 *   category / term  the term description, otherwise a generated sentence
 *   shop, blog, home a fixed sentence below (blog/home use their page's box first)
 * Search engines show roughly 155 characters, so everything is cut to that.
 */

const WEARIT_META_DESCRIPTION_LENGTH = 155;

/**
 * Fallback for the whole site (and for pages with no text of their own).
 */
function wearit_default_meta_description() {
    return 'Heavyweight streetwear built to last. Shop WearIt hoodies, tees, jackets, cargo pants, jeans and sneakers — new drops, fair prices.';
}

// ── Admin field ───────────────────────────────────────────────

add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    // One OR-group per public post type. acf/init runs before the theme's own post types
    // are registered on init, so those are listed by name; others (e.g. testimonials) are picked up.
    $types = array_unique( array_merge(
        [ 'post', 'page', 'product', 'initiative' ],
        array_values( get_post_types( [ 'public' => true ] ) )
    ) );
    $location = [];
    foreach ( $types as $type ) {
        if ( $type !== 'attachment' ) {
            $location[] = [ [ 'param' => 'post_type', 'operator' => '==', 'value' => $type ] ];
        }
    }

    acf_add_local_field_group( [
        'key'      => 'group_wearit_seo',
        'title'    => 'SEO',
        'fields'   => [
            [
                'key'          => 'field_wearit_meta_description',
                'label'        => 'Meta description',
                'name'         => 'meta_description',
                'type'         => 'textarea',
                'instructions' => 'The text Google shows under the page title. Aim for 120–155 characters and use the words people search for. Leave empty to generate it automatically.',
                'rows'         => 4,
                'maxlength'    => 160,
                'new_lines'    => '',
            ],
        ],
        'location' => $location,
        'position' => 'side',
    ] );
} );

// ── Building the description ──────────────────────────────────

/**
 * Plain text, one line, cut at a word boundary to the length search engines show.
 */
function wearit_clean_meta_description( $text ) {
    $text = wp_strip_all_tags( strip_shortcodes( (string) $text ) );
    $text = preg_replace( '/\s+/u', ' ', html_entity_decode( $text, ENT_QUOTES, 'UTF-8' ) );
    $text = trim( $text, " `" ); // some excerpts are pasted wrapped in `backticks`

    if ( mb_strlen( $text ) > WEARIT_META_DESCRIPTION_LENGTH ) {
        $cut  = mb_substr( $text, 0, WEARIT_META_DESCRIPTION_LENGTH - 1 );
        $text = preg_replace( '/\s+\S*$/u', '', $cut ) . '…';
    }
    return $text;
}

/**
 * The "Meta description" box of one post/page ('' when empty).
 */
function wearit_manual_meta_description( $post_id ) {
    return $post_id ? trim( (string) get_post_meta( $post_id, 'meta_description', true ) ) : '';
}

/**
 * Description for a single post, page, product or initiative.
 */
function wearit_post_meta_description( $post ) {
    $manual = wearit_manual_meta_description( $post->ID );
    if ( $manual !== '' ) {
        return $manual;
    }

    $excerpt = has_excerpt( $post ) ? $post->post_excerpt : '';

    // "Heavyweight brushed-fleece hoodie… Black, sizes S–XXL, $20 at WearIt."
    if ( $post->post_type === 'product' && function_exists( 'wearit_get_product' ) ) {
        $product = wearit_get_product( $post->ID );
        $details = array_filter( [
            $product['color_label'],
            $product['size_range'] ? 'sizes ' . $product['size_range'] : '',
            $product['price_formatted'] . ' at WearIt.',
        ] );
        $intro = $excerpt !== '' ? rtrim( wp_strip_all_tags( $excerpt ), '. ' ) . '. ' : $product['title'] . ' — ';
        return $intro . implode( ', ', $details );
    }

    if ( $excerpt !== '' ) {
        return $excerpt;
    }
    return $post->post_content;
}

/**
 * The description for the current request ('' = print nothing).
 */
function wearit_meta_description() {
    // Home page (static front page)
    if ( is_front_page() ) {
        $manual = wearit_manual_meta_description( (int) get_option( 'page_on_front' ) );
        return $manual !== '' ? $manual : wearit_default_meta_description();
    }

    // Blog page (page_for_posts)
    if ( is_home() ) {
        $manual = wearit_manual_meta_description( (int) get_option( 'page_for_posts' ) );
        return $manual !== '' ? $manual : 'The WearIt blog: streetwear style guides, outfit ideas on a student budget, trends and news about our latest drops.';
    }

    // Shop page, also with filters in the URL
    if ( is_post_type_archive( 'product' ) ) {
        $names = [];
        if ( function_exists( 'wearit_get_active_filters' ) ) {
            foreach ( wearit_get_active_filters()['product_cat'] as $slug ) {
                $term = get_term_by( 'slug', $slug, 'product_category' );
                if ( $term ) {
                    $names[] = $term->name;
                }
            }
        }
        if ( $names ) {
            return 'Shop WearIt ' . implode( ', ', $names ) . ': heavyweight streetwear built to last, in every size we make. New drops, fair prices.';
        }
        return 'Shop all WearIt streetwear: heavyweight hoodies, tees, jackets, cargo pants, jeans and sneakers. Filter by category and color.';
    }

    // Sustainability (initiative archive)
    if ( is_post_type_archive( 'initiative' ) ) {
        return 'How WearIt gives back: the Impact Collection, Wear It Forward donations and the steps we take to make streetwear that lasts.';
    }

    // Blog categories and product categories
    if ( is_category() || is_tax() || is_tag() ) {
        $term = get_queried_object();
        if ( $term && trim( (string) $term->description ) !== '' ) {
            return $term->description;
        }
        if ( $term && is_tax( 'product_category' ) ) {
            return 'Shop WearIt ' . $term->name . ': heavyweight streetwear built to last. New drops, fair prices.';
        }
        if ( $term ) {
            return 'WearIt blog posts about ' . $term->name . ': streetwear style guides, outfit ideas and trends.';
        }
    }

    if ( is_singular() ) {
        $text = wearit_post_meta_description( get_queried_object() );
        return trim( wp_strip_all_tags( (string) $text ) ) !== '' ? $text : wearit_default_meta_description();
    }

    return ''; // 404, search, date archives
}

add_action( 'wp_head', function () {
    $description = wearit_clean_meta_description( wearit_meta_description() );
    if ( $description !== '' ) {
        echo '<meta name="description" content="' . esc_attr( $description ) . '" />' . "\n";
    }
}, 1 );
