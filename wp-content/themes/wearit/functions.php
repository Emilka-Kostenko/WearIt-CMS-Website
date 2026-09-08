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

// ── Handle contact form submission ─────────────────────────────

add_action( 'admin_post_wearit_contact', 'wearit_handle_contact_form' );
add_action( 'admin_post_nopriv_wearit_contact', 'wearit_handle_contact_form' );

function wearit_handle_contact_form() {
    // Verify the nonce.
    if ( ! isset( $_POST['wearit_contact_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_contact_nonce'], 'wearit_contact_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }

    // Sanitize input.
    $name  = isset( $_POST['contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : '';
    $email = isset( $_POST['contact_email'] ) ? sanitize_email( wp_unslash( $_POST['contact_email'] ) ) : '';

    if ( empty( $name ) || empty( $email ) || ! is_email( $email ) ) {
        wp_safe_redirect( add_query_arg( 'contact', 'error', wp_get_referer() ) );
        exit;
    }

    // Build and send the email to admin.
    $to      = get_option( 'admin_email' );
    $subject = sprintf( 'New contact form submission from %s', $name );
    $message = sprintf( "Name: %s\nEmail: %s", $name, $email );
    $headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $email );

    $sent = wp_mail( $to, $subject, $message, $headers );

    wp_safe_redirect( add_query_arg( 'contact', $sent ? 'success' : 'error', wp_get_referer() ) );
    exit;
}