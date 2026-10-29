<?php
/**
 * Customer accounts — log in, register, log out, and what needs an account.
 *
 * The page body is accountPage.php (template "Account Page").
 * The forms post to admin-post.php; the handlers live further down this file.
 *
 * Logged-in only:
 *   checkout       checkoutPage.php shows a log-in box, wearit_handle_checkout() refuses (inc/checkout.php)
 *   comments       single.php shows a log-in box, WordPress refuses via the comment_registration filter below
 *   contact mail   contact.php shows a log-in box, wearit_handle_contact_form() refuses (functions.php)
 *
 * Every "log in" link carries ?redirect_to= so the customer lands back where they were.
 */

const WEARIT_PASSWORD_MIN_LENGTH = 8;

// ── The account page ──────────────────────────────────────────

/**
 * ID of the account page: the page using the "Account Page" template,
 * or else the page with slug "account". 0 when neither exists.
 */
function wearit_account_page_id() {
    static $id = null;
    if ( $id !== null ) {
        return $id;
    }

    if ( is_page_template( 'accountPage.php' ) ) {
        $id = get_queried_object_id();
        return $id;
    }

    $pages = get_posts( [
        'post_type'   => 'page',
        'post_status' => 'publish',
        'meta_key'    => '_wp_page_template',
        'meta_value'  => 'accountPage.php',
        'numberposts' => 1,
        'fields'      => 'ids',
    ] );
    if ( $pages ) {
        $id = (int) $pages[0];
        return $id;
    }

    $page = get_page_by_path( 'account' );
    $id   = $page ? (int) $page->ID : 0;
    return $id;
}

/**
 * URL of the account page, optionally sending the customer back to $redirect after logging in.
 * $view 'register' is the Create Account page (/account/?view=register); otherwise Log In.
 */
function wearit_account_url( $redirect = '', $view = '' ) {
    $id  = wearit_account_page_id();
    $url = $id ? get_permalink( $id ) : home_url( '/account/' );

    $args = [];
    if ( $view === 'register' ) {
        $args['view'] = 'register';
    }
    if ( $redirect ) {
        $args['redirect_to'] = rawurlencode( $redirect );
    }
    return $args ? add_query_arg( $args, $url ) : $url;
}

/**
 * Which page the account template shows: 'account' (logged in), 'register' or 'login'.
 */
function wearit_account_view() {
    if ( is_user_logged_in() ) {
        return 'account';
    }
    return ( isset( $_GET['view'] ) && $_GET['view'] === 'register' ) ? 'register' : 'login';
}

// Each view is its own page for browsers and screen readers: "Create Account – Wear It." etc.
add_filter( 'document_title_parts', function ( $parts ) {
    if ( wearit_account_page_id() && is_page( wearit_account_page_id() ) ) {
        $titles = [ 'account' => 'Your Account', 'register' => 'Create Account', 'login' => 'Log In' ];
        $parts['title'] = $titles[ wearit_account_view() ];
    }
    return $parts;
} );

/**
 * Where to send the customer after logging in or registering: ?redirect_to= when it
 * points at this site, otherwise the account page itself.
 */
function wearit_account_redirect_target() {
    $raw = isset( $_REQUEST['redirect_to'] ) ? wp_unslash( $_REQUEST['redirect_to'] ) : '';
    return wp_validate_redirect( esc_url_raw( (string) $raw ), wearit_account_url() );
}

/**
 * The message for ?account=… after a form came back.
 * 'fields' are the inputs the message is about: the template marks them aria-invalid
 * and points them at the message, so screen readers read it with the field.
 *
 * @return array|null [ 'type' => 'error'|'success', 'text' => string, 'fields' => string[] ]
 */
function wearit_account_notice() {
    $code = isset( $_GET['account'] ) ? sanitize_key( $_GET['account'] ) : '';
    $notices = [
        'login_failed'    => [ 'error', 'That email and password don’t match. Check them and try again.', [ 'login_email', 'login_password' ] ],
        'login_missing'   => [ 'error', 'Fill in your email and password.', [ 'login_email', 'login_password' ] ],
        'register_fields' => [ 'error', 'Fill in your name, a valid email address (like name@email.com) and a password.', [ 'register_name', 'register_email', 'register_password' ] ],
        'register_exists' => [ 'error', 'There is already an account with that email. Log in below instead.', [] ],
        'register_short'  => [ 'error', 'Your password needs at least ' . WEARIT_PASSWORD_MIN_LENGTH . ' characters.', [ 'register_password' ] ],
        'register_match'  => [ 'error', 'The two passwords don’t match. Type the same password in both fields.', [ 'register_password', 'register_password2' ] ],
        'register_error'  => [ 'error', 'Something went wrong creating your account. Please try again.', [] ],
        'logged_out'      => [ 'success', 'You are logged out.', [] ],
    ];
    if ( ! isset( $notices[ $code ] ) ) {
        return null;
    }
    [ $type, $text, $fields ] = $notices[ $code ];
    return [ 'type' => $type, 'text' => $text, 'fields' => $fields ];
}

/**
 * Attributes for one input: aria-invalid + aria-describedby when the notice is about it,
 * plus any hint ids ('account-password-hint'). Already escaped.
 */
function wearit_account_field_attrs( $name, $notice, array $hints = [] ) {
    $describedby = $hints;
    $invalid     = $notice && in_array( $name, $notice['fields'], true );
    if ( $invalid ) {
        array_unshift( $describedby, 'account-notice' );
    }
    $out = $invalid ? ' aria-invalid="true"' : '';
    if ( $describedby ) {
        $out .= ' aria-describedby="' . esc_attr( implode( ' ', $describedby ) ) . '"';
    }
    return $out;
}

/**
 * Send a form back to the account page with ?account=$code, keeping redirect_to.
 */
function wearit_account_bounce( $code, $view = '' ) {
    $raw = isset( $_POST['redirect_to'] ) ? wp_unslash( $_POST['redirect_to'] ) : '';
    $url = add_query_arg( 'account', $code, wearit_account_url( wp_validate_redirect( esc_url_raw( (string) $raw ), '' ), $view ) );
    wp_safe_redirect( $url );
    exit;
}

// Already logged in and sent here from checkout, a comment form etc.: go straight back.
add_action( 'template_redirect', function () {
    if ( is_user_logged_in() && is_page( wearit_account_page_id() ) && wearit_account_page_id() && ! empty( $_GET['redirect_to'] ) ) {
        wp_safe_redirect( wearit_account_redirect_target() );
        exit;
    }
} );

// ── Log in ────────────────────────────────────────────────────

add_action( 'admin_post_nopriv_wearit_login', 'wearit_handle_login' );
add_action( 'admin_post_wearit_login', 'wearit_handle_login' ); // already logged in: just send them on

function wearit_handle_login() {
    if ( ! isset( $_POST['wearit_login_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_login_nonce'], 'wearit_login_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }
    if ( is_user_logged_in() ) {
        wp_safe_redirect( wearit_account_redirect_target() );
        exit;
    }

    $email    = sanitize_text_field( wp_unslash( $_POST['login_email'] ?? '' ) );
    $password = (string) wp_unslash( $_POST['login_password'] ?? '' ); // never sanitized: it would change the password

    if ( $email === '' || $password === '' ) {
        wearit_account_bounce( 'login_missing' );
    }

    // WordPress accepts the email here as well as the username.
    $user = wp_signon( [
        'user_login'    => $email,
        'user_password' => $password,
        'remember'      => ! empty( $_POST['login_remember'] ),
    ], is_ssl() );

    if ( is_wp_error( $user ) ) {
        wearit_account_bounce( 'login_failed' );
    }

    wp_safe_redirect( wearit_account_redirect_target() );
    exit;
}

// ── Register ──────────────────────────────────────────────────

add_action( 'admin_post_nopriv_wearit_register', 'wearit_handle_register' );
add_action( 'admin_post_wearit_register', 'wearit_handle_register' );

function wearit_handle_register() {
    if ( ! isset( $_POST['wearit_register_nonce'] ) || ! wp_verify_nonce( $_POST['wearit_register_nonce'], 'wearit_register_nonce' ) ) {
        wp_die( 'Security check failed.' );
    }
    if ( is_user_logged_in() ) {
        wp_safe_redirect( wearit_account_redirect_target() );
        exit;
    }

    // Hidden field real people never see. Bots fill it in; pretend it worked.
    if ( ! empty( $_POST['register_website'] ) ) {
        wp_safe_redirect( home_url( '/' ) );
        exit;
    }

    $name      = sanitize_text_field( wp_unslash( $_POST['register_name'] ?? '' ) );
    $email     = sanitize_email( wp_unslash( $_POST['register_email'] ?? '' ) );
    $password  = (string) wp_unslash( $_POST['register_password'] ?? '' );
    $password2 = (string) wp_unslash( $_POST['register_password2'] ?? '' );

    if ( $name === '' || ! is_email( $email ) || $password === '' ) {
        wearit_account_bounce( 'register_fields', 'register' );
    }
    if ( email_exists( $email ) ) {
        // Usually the same form sent twice (a second click while the first was still saving):
        // the first request already created the account. If the password matches it,
        // log them in instead of showing "already exists".
        $existing = wp_signon( [
            'user_login'    => $email,
            'user_password' => $password,
            'remember'      => false,
        ], is_ssl() );
        if ( ! is_wp_error( $existing ) ) {
            wp_safe_redirect( wearit_account_redirect_target() );
            exit;
        }
        wearit_account_bounce( 'register_exists' );
    }
    if ( mb_strlen( $password ) < WEARIT_PASSWORD_MIN_LENGTH ) {
        wearit_account_bounce( 'register_short', 'register' );
    }
    if ( $password !== $password2 ) {
        wearit_account_bounce( 'register_match', 'register' );
    }

    // Username from the part before the @, made unique: "anna", "anna2", …
    $base  = sanitize_user( strstr( $email, '@', true ), true );
    $base  = $base !== '' ? $base : 'customer';
    $login = $base;
    for ( $n = 2; username_exists( $login ); $n++ ) {
        $login = $base . $n;
    }

    $user_id = wp_insert_user( [
        'user_login'   => $login,
        'user_email'   => $email,
        'user_pass'    => $password,
        'display_name' => $name,
        'first_name'   => $name,
        'role'         => 'subscriber', // always: never trust the site's default_role setting for a public form
    ] );

    if ( is_wp_error( $user_id ) ) {
        error_log( 'wearit_register: ' . $user_id->get_error_message() );
        wearit_account_bounce( 'register_error', 'register' );
    }

    // Log the new customer straight in.
    wp_set_current_user( $user_id );
    wp_set_auth_cookie( $user_id, false, is_ssl() );

    wp_safe_redirect( wearit_account_redirect_target() );
    exit;
}

// ── What needs an account ─────────────────────────────────────

// Comments: same as ticking "Users must be registered and logged in to comment" in
// Settings → Discussion, but in code so it travels with the theme. WordPress then
// refuses comments from logged-out visitors in wp-comments-post.php.
add_filter( 'pre_option_comment_registration', function () {
    return 1;
} );

// Customers don't need the black WordPress toolbar over the navbar.
add_filter( 'show_admin_bar', function ( $show ) {
    return current_user_can( 'edit_posts' ) ? $show : false;
} );
