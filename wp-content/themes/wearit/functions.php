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
        '1.0.1'
    );
} );

// ── Register Initiative custom post type ──────────────────────
add_action( 'init', function () {
    register_post_type( 'initiative', [
        'labels' => [
            'name'          => 'Initiatives',
            'singular_name' => 'Initiative',
            'add_new_item'  => 'Add New Initiative',
            'edit_item'     => 'Edit Initiative',
            'all_items'     => 'All Initiatives',
        ],
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'sustainability' ],
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
        'show_in_rest' => false,
        'menu_icon'    => 'dashicons-leaf',
    ] );
} );

// ── Products (post type, fields, shop filtering, demo data) ───
require_once get_template_directory() . '/inc/products.php';
require_once get_template_directory() . '/inc/seed-products.php';

// ── Cart (cookie storage, mini-cart in the header) ────────────
require_once get_template_directory() . '/inc/cart.php';

// ── Home hero slider (Black Week slide, arrows, autoplay) ─────
require_once get_template_directory() . '/inc/hero-slider.php';

// ── Orders (order post type, saved on checkout) ───────────────
require_once get_template_directory() . '/inc/orders.php';

// ── Checkout (form, order summary, placing the order) ─────────
require_once get_template_directory() . '/inc/checkout.php';

// ── Order emails (confirmation, shop notification, shipped) ───
require_once get_template_directory() . '/inc/order-emails.php';

// ── SEO (meta descriptions + "Meta description" admin box) ────
require_once get_template_directory() . '/inc/seo.php';

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

/**
 * VIP early-access signup (front page modal).
 * Stores emails in the wp_options table and emails the site admin.
 */
function wearit_vip_signup() {
  check_ajax_referer( 'wearit_vip_signup', 'nonce' );

  // Honeypot: bots fill this in, people never see it.
  if ( ! empty( $_POST['website'] ) ) {
    wp_send_json_success( [ 'message' => "You're on the list." ] );
  }

  $email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
  if ( ! is_email( $email ) ) {
    wp_send_json_error( [ 'message' => 'Please enter a valid email address.' ] );
  }

  $signups = get_option( 'wearit_vip_signups', [] );
  $emails  = wp_list_pluck( $signups, 'email' );

  if ( ! in_array( $email, $emails, true ) ) {
    $signups[] = [ 'email' => $email, 'time' => current_time( 'mysql' ) ];
    update_option( 'wearit_vip_signups', $signups, false );

    wp_mail(
      get_option( 'admin_email' ),
      'New VIP signup: ' . $email,
      "New VIP early-access signup:\n\n" . $email . "\n\nTotal so far: " . count( $signups )
    );
  }

  // Same answer whether new or already signed up, so emails can't be probed.
  wp_send_json_success( [ 'message' => "You're on the list. Watch your inbox before Thursday 20:00." ] );
}
add_action( 'wp_ajax_wearit_vip_signup', 'wearit_vip_signup' );
add_action( 'wp_ajax_nopriv_wearit_vip_signup', 'wearit_vip_signup' );