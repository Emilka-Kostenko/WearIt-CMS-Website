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
    error_log( 'wearit_contact: handler fired' );

    if ( ! isset( $_POST['wearit_contact_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_contact_nonce'], 'wearit_contact_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }

    $name    = sanitize_text_field( wp_unslash( $_POST['contact_name'] ?? '' ) );
    $email   = sanitize_email( wp_unslash( $_POST['contact_email'] ?? '' ) );
    $subject = sanitize_text_field( wp_unslash( $_POST['contact_subject'] ?? '' ) );
    $message = sanitize_textarea_field( wp_unslash( $_POST['contact_message'] ?? '' ) );

    $redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );

    if ( empty( $name ) || ! is_email( $email ) || empty( $subject ) || empty( $message ) ) {
        error_log( 'wearit_contact: validation failed' );
        wp_safe_redirect( add_query_arg( 'contact', 'invalid', $redirect ) );
        exit;
    }

    $to      = get_option( 'admin_email' );
    $body    = "Name: {$name}\nEmail: {$email}\nSubject: {$subject}\n\nMessage:\n{$message}";
    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Reply-To: ' . $name . ' <' . $email . '>',
    );

    $sent = wp_mail( $to, '[WearIt Contact] ' . $subject, $body, $headers );
    error_log( 'wearit_contact: wp_mail returned ' . var_export( $sent, true ) );

    wp_safe_redirect( add_query_arg( 'contact', $sent ? 'success' : 'error', $redirect ) );
    exit;
}