<?php
/**
 * Home page hero slider: slide 1 is the regular hero (its fields are the existing
 * "Hero section" group on the Home page), slide 2 is the Black Week slide.
 *
 * Everything on slide 2, and the autoplay speed, is edited in the admin:
 *   Pages → Home → "Hero slider — Black Week slide".
 * The template is template-parts/hero-black-week.php, the script is js/hero-slider.js.
 */

// ── Admin fields (registered in code so every teammate gets them via git) ──

add_action( 'acf/init', function () {
    if ( ! function_exists( 'acf_add_local_field_group' ) ) {
        return;
    }

    acf_add_local_field_group( [
        'key'    => 'group_wearit_hero_slider',
        'title'  => 'Hero slider — Black Week slide',
        'fields' => [
            [
                'key'           => 'field_wearit_bw_enabled',
                'label'         => 'Show the Black Week slide',
                'name'          => 'bw_enabled',
                'type'          => 'true_false',
                'instructions'  => 'Switch off when the sale is over: the hero goes back to the single regular image, without arrows.',
                'ui'            => 1,
                'default_value' => 1,
            ],
            [
                'key'           => 'field_wearit_hero_seconds',
                'label'         => 'Seconds per slide',
                'name'          => 'hero_seconds',
                'type'          => 'number',
                'instructions'  => 'How long each slide stays before the next one appears. Enter 0 to turn automatic sliding off (visitors can still use the arrows).',
                'default_value' => 3,
                'min'           => 0,
                'max'           => 60,
                'step'          => 0.5,
                'append'        => 'seconds',
            ],
            [
                'key'           => 'field_wearit_bw_tag',
                'label'         => 'Small label',
                'name'          => 'bw_tag',
                'type'          => 'text',
                'default_value' => 'Members Only · 24–30 Nov',
            ],
            [
                'key'           => 'field_wearit_bw_heading',
                'label'         => 'Heading',
                'name'          => 'bw_heading',
                'type'          => 'text',
                'instructions'  => 'Each word goes on its own line.',
                'default_value' => 'Black Week',
            ],
            [
                'key'           => 'field_wearit_bw_text',
                'label'         => 'Text',
                'name'          => 'bw_text',
                'type'          => 'textarea',
                'rows'          => 3,
                'default_value' => 'Our biggest sale of the year. One week, every piece, no exclusions. Log in as a member and the discount applies at checkout. Small runs go fast, and nothing gets restocked.',
            ],
            [
                'key'           => 'field_wearit_bw_primary_label',
                'label'         => 'Main button — text',
                'name'          => 'bw_primary_label',
                'type'          => 'text',
                'default_value' => 'Shop Black Week',
            ],
            [
                'key'          => 'field_wearit_bw_primary_url',
                'label'        => 'Main button — link',
                'name'         => 'bw_primary_url',
                'type'         => 'url',
                'instructions' => 'Leave empty to link to the shop page.',
            ],
            [
                'key'           => 'field_wearit_bw_secondary_label',
                'label'         => 'Second button — text',
                'name'          => 'bw_secondary_label',
                'type'          => 'text',
                'default_value' => 'Become a Member',
            ],
            [
                'key'   => 'field_wearit_bw_secondary_url',
                'label' => 'Second button — link',
                'name'  => 'bw_secondary_url',
                'type'  => 'url',
            ],
            [
                'key'           => 'field_wearit_bw_discount',
                'label'         => 'Big number',
                'name'          => 'bw_discount',
                'type'          => 'text',
                'instructions'  => 'Just the number, e.g. 30. The % sign is added automatically.',
                'default_value' => '30',
            ],
            [
                'key'           => 'field_wearit_bw_discount_label',
                'label'         => 'Text under the big number',
                'name'          => 'bw_discount_label',
                'type'          => 'text',
                'default_value' => 'Off everything',
            ],
        ],
        'location' => [
            [ [ 'param' => 'page_type', 'operator' => '==', 'value' => 'front_page' ] ],
        ],
        'position'   => 'normal',
        'menu_order' => 1,
    ] );
} );

// ── Reading the settings ──────────────────────────────────────

/**
 * Everything the hero slider needs, with the design's text as fallback for empty fields.
 *
 * @return array enabled (bool), seconds (float, 0 = no autoplay), tag, heading,
 *               text, primary_label, primary_url, secondary_label, secondary_url,
 *               discount, discount_label
 */
function wearit_get_hero_slider() {
    $post_id = (int) get_option( 'page_on_front' );
    if ( ! $post_id ) {
        $post_id = (int) get_the_ID();
    }

    $get = function ( $name, $default ) use ( $post_id ) {
        $value = function_exists( 'get_field' ) ? get_field( $name, $post_id ) : get_post_meta( $post_id, $name, true );
        return ( $value === null || $value === '' ) ? $default : $value;
    };

    $enabled = $get( 'bw_enabled', 1 );

    return [
        'enabled'         => (bool) $enabled,
        'seconds'         => max( 0, (float) $get( 'hero_seconds', 3 ) ),
        'tag'             => (string) $get( 'bw_tag', 'Members Only · 24–30 Nov' ),
        'heading'         => (string) $get( 'bw_heading', 'Black Week' ),
        'text'            => (string) $get( 'bw_text', 'Our biggest sale of the year. One week, every piece, no exclusions. Log in as a member and the discount applies at checkout. Small runs go fast, and nothing gets restocked.' ),
        'primary_label'   => (string) $get( 'bw_primary_label', 'Shop Black Week' ),
        'primary_url'     => (string) $get( 'bw_primary_url', get_post_type_archive_link( 'product' ) ?: '#' ),
        'secondary_label' => (string) $get( 'bw_secondary_label', 'Become a Member' ),
        'secondary_url'   => (string) $get( 'bw_secondary_url', '#' ),
        'discount'        => (string) $get( 'bw_discount', '30' ),
        'discount_label'  => (string) $get( 'bw_discount_label', 'Off everything' ),
    ];
}

// ── Script (front page only) ──────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
    if ( ! is_front_page() ) {
        return;
    }
    $file = get_template_directory() . '/js/hero-slider.js';
    wp_enqueue_script(
        'wearit-hero-slider',
        get_template_directory_uri() . '/js/hero-slider.js',
        [],
        file_exists( $file ) ? filemtime( $file ) : '1.0.0',
        [ 'in_footer' => true, 'strategy' => 'defer' ]
    );
} );
