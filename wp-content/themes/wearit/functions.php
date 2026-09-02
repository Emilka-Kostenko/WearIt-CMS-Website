<?php

// ── Theme setup ───────────────────────────────────────────────
add_action( 'after_setup_theme', function () {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'comment-list', 'comment-form', 'search-form' ] );

    // Primary navigation menu
    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'wearit' ),
    ] );
} );

// ── Enqueue styles & scripts ──────────────────────────────────
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'wearit-style',
        get_template_directory_uri() . '/css/style.css',
        [],
        '1.0.0'
    );
} );
