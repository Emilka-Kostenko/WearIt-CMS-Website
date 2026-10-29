<?php
/**
 * "Log in to continue" box. Shown instead of a form that needs an account
 * (checkout, comments, contact). See inc/account.php.
 *
 * Args (get_template_part( 'template-parts/login-required', null, [ … ] )):
 *   context   checkout | comments | contact
 *   title     heading, e.g. "Log in to check out"
 *   text      one sentence under it
 *   redirect  where to come back to after logging in (defaults to the current page)
 */
$context  = sanitize_key( $args['context'] ?? 'default' );
$title    = wearit_account_text( 'login_box_' . $context . '_title', $args['title'] ?? 'Log in to continue' );
$text     = wearit_account_text( 'login_box_' . $context . '_text', $args['text'] ?? 'You need an account for this. It only takes a minute.' );
$redirect = $args['redirect'] ?? ( is_singular() ? get_permalink() : home_url( add_query_arg( [] ) ) );
?>
<div class="login-required">
  <h2 class="login-required__title"><?php echo esc_html( $title ); ?></h2>
  <p class="login-required__text"><?php echo esc_html( $text ); ?></p>
  <div class="login-required__actions">
    <a href="<?php echo esc_url( wearit_account_url( $redirect ) ); ?>" class="login-required__button login-required__button--primary"><?php echo esc_html( wearit_account_text( 'login_box_login_button', 'Log In' ) ); ?></a>
    <a href="<?php echo esc_url( wearit_account_url( $redirect, 'register' ) ); ?>" class="login-required__button"><?php echo esc_html( wearit_account_text( 'login_box_register_button', 'Create Account' ) ); ?></a>
  </div>
</div>
