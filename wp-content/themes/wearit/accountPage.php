<?php
/**
 * Template Name: Account Page
 *
 * Three pages in one template (see wearit_account_view() in inc/account.php):
 *   /account/                 Log In
 *   /account/?view=register   Create Account
 *   /account/  (logged in)    the account overview
 * Same layout as the cart and checkout pages: hero, then one panel.
 * Form handling, redirects, messages and page titles live in inc/account.php.
 *
 * Accessibility (WCAG 2.2 AA): every input has a visible <label>, autocomplete for
 * password managers (1.3.5), errors are text, focused on load and tied to the fields
 * with aria-invalid / aria-describedby (3.3.1, 3.3.3), hints are linked the same way.
 */
get_header();

$view     = wearit_account_view();
$notice   = wearit_account_notice();
$redirect = isset( $_GET['redirect_to'] ) ? wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ), '' ) : '';
$user     = wp_get_current_user();
$cart     = wearit_get_cart();
?>

<main class="shop account-page" id="main">

  <!-- ── HERO ── -->
  <section class="shop-hero">
    <div class="shop-hero__container">
      <?php if ( $view === 'account' ) : ?>
        <p class="shop-hero__eyebrow">Your account</p>
        <h1 class="shop-hero__heading">Hi, <span class="shop-hero__heading--accent"><?php echo esc_html( $user->display_name ); ?></span></h1>
      <?php elseif ( $view === 'register' ) : ?>
        <p class="shop-hero__eyebrow">New here</p>
        <h1 class="shop-hero__heading">Create <span class="shop-hero__heading--accent">Account</span></h1>
      <?php else : ?>
        <p class="shop-hero__eyebrow"><?php echo $redirect ? 'Log in to continue' : 'Members'; ?></p>
        <h1 class="shop-hero__heading">Log <span class="shop-hero__heading--accent">In</span></h1>
      <?php endif; ?>
    </div>
  </section>

  <section class="shop-body">
    <div class="shop-body__container account-body">

      <?php if ( $notice ) : ?>
        <!-- errors get focus on load (script below) so screen readers announce them; success just reads politely -->
        <p
          class="account-notice account-notice--<?php echo esc_attr( $notice['type'] ); ?>"
          id="account-notice"
          tabindex="-1"
          role="<?php echo $notice['type'] === 'error' ? 'alert' : 'status'; ?>"
        >
          <?php if ( $notice['type'] === 'error' ) : ?><span class="account-notice__label">Error:</span> <?php endif; ?>
          <?php echo esc_html( $notice['text'] ); ?>
        </p>
      <?php endif; ?>

      <?php if ( $view === 'account' ) : ?>

        <!-- ── LOGGED IN: overview ── -->
        <div class="checkout-fields account-panel">
          <h2 class="checkout-fields__title">Details</h2>

          <dl class="account-details">
            <div>
              <dt>Name</dt>
              <dd><?php echo esc_html( $user->display_name ); ?></dd>
            </div>
            <div>
              <dt>Email</dt>
              <dd><?php echo esc_html( $user->user_email ); ?></dd>
            </div>
            <div>
              <dt>Member since</dt>
              <dd><?php echo esc_html( date_i18n( 'j F Y', strtotime( $user->user_registered ) ) ); ?></dd>
            </div>
          </dl>

          <div class="account-actions">
            <?php if ( $cart['items'] ) : ?>
              <a href="<?php echo esc_url( wearit_checkout_url() ); ?>" class="cart-button cart-button--primary">Go to Checkout</a>
            <?php else : ?>
              <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="cart-button cart-button--primary">Shop Products</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( wp_logout_url( add_query_arg( 'account', 'logged_out', wearit_account_url() ) ) ); ?>" class="cart-button cart-button--ghost">Log Out</a>
          </div>
        </div>

      <?php elseif ( $view === 'register' ) : ?>

        <!-- ── CREATE ACCOUNT ── -->
        <form class="checkout-fields account-panel" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" aria-labelledby="register-title">
          <input type="hidden" name="action" value="wearit_register" />
          <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
          <?php wp_nonce_field( 'wearit_register_nonce', 'wearit_register_nonce' ); ?>

          <h2 class="checkout-fields__title" id="register-title">Join WearIt</h2>
          <p class="account-intro">A free account lets you check out, comment on posts and message us. All fields are required.</p>

          <div class="checkout-field">
            <label for="register_name">Name</label>
            <input type="text" id="register_name" name="register_name" class="checkout-field__input" autocomplete="name" required<?php echo wearit_account_field_attrs( 'register_name', $notice ); ?> />
          </div>
          <div class="checkout-field">
            <label for="register_email">Email</label>
            <input type="email" id="register_email" name="register_email" class="checkout-field__input" placeholder="name@email.com" autocomplete="email" required<?php echo wearit_account_field_attrs( 'register_email', $notice ); ?> />
          </div>
          <div class="checkout-field">
            <label for="register_password">Password</label>
            <input type="password" id="register_password" name="register_password" class="checkout-field__input" minlength="<?php echo esc_attr( WEARIT_PASSWORD_MIN_LENGTH ); ?>" autocomplete="new-password" required<?php echo wearit_account_field_attrs( 'register_password', $notice, [ 'account-password-hint' ] ); ?> />
            <p class="account-hint" id="account-password-hint">At least <?php echo esc_html( WEARIT_PASSWORD_MIN_LENGTH ); ?> characters.</p>
          </div>
          <div class="checkout-field">
            <label for="register_password2">Repeat password</label>
            <input type="password" id="register_password2" name="register_password2" class="checkout-field__input" minlength="<?php echo esc_attr( WEARIT_PASSWORD_MIN_LENGTH ); ?>" autocomplete="new-password" required<?php echo wearit_account_field_attrs( 'register_password2', $notice ); ?> />
          </div>

          <!-- spam trap: off-screen and skipped by keyboard + screen readers; bots fill it in (see wearit_handle_register) -->
          <div class="account-trap" aria-hidden="true">
            <label for="register_website">Leave this empty</label>
            <input type="text" id="register_website" name="register_website" tabindex="-1" autocomplete="off" />
          </div>

          <button type="submit" class="cart-button cart-button--primary account-submit" data-busy-label="Creating account…">Create Account</button>

          <p class="account-switch">
            Already have an account?
            <a href="<?php echo esc_url( wearit_account_url( $redirect ) ); ?>" class="account-link">Log in</a>
          </p>
        </form>

      <?php else : ?>

        <!-- ── LOG IN ── -->
        <form class="checkout-fields account-panel" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" aria-labelledby="login-title">
          <input type="hidden" name="action" value="wearit_login" />
          <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>" />
          <?php wp_nonce_field( 'wearit_login_nonce', 'wearit_login_nonce' ); ?>

          <h2 class="checkout-fields__title" id="login-title">Welcome back</h2>
          <p class="account-intro">Log in with the email and password you signed up with.</p>

          <div class="checkout-field">
            <label for="login_email">Email</label>
            <input type="text" id="login_email" name="login_email" class="checkout-field__input" placeholder="name@email.com" autocomplete="username" inputmode="email" required<?php echo wearit_account_field_attrs( 'login_email', $notice ); ?> />
          </div>
          <div class="checkout-field">
            <label for="login_password">Password</label>
            <input type="password" id="login_password" name="login_password" class="checkout-field__input" autocomplete="current-password" required<?php echo wearit_account_field_attrs( 'login_password', $notice ); ?> />
          </div>

          <div class="account-remember">
            <input type="checkbox" id="login_remember" name="login_remember" value="1" />
            <label for="login_remember">Keep me logged in</label>
          </div>

          <button type="submit" class="cart-button cart-button--primary account-submit" data-busy-label="Logging in…">Log In</button>

          <p class="account-forgot">
            <a href="<?php echo esc_url( wp_lostpassword_url( wearit_account_url() ) ); ?>" class="account-link">Forgot your password?</a>
          </p>

          <p class="account-switch">
            New to WearIt?
            <a href="<?php echo esc_url( wearit_account_url( $redirect, 'register' ) ); ?>" class="account-link">Create an account</a>
          </p>
        </form>

      <?php endif; ?>

    </div>
  </section>

</main>

<script>
  // Send each form only once. Saving an account takes a moment (remote database), and a
  // second click used to send it again — that second request then saw the account the
  // first one had just created and showed "already exists".
  document.querySelectorAll('form.account-panel').forEach((form) => {
    form.addEventListener('submit', (e) => {
      if (form.dataset.sending) {
        e.preventDefault();
        return;
      }
      form.dataset.sending = '1';
      form.setAttribute('aria-busy', 'true');
      const button = form.querySelector('button[type="submit"]');
      // aria-disabled instead of disabled: the button keeps focus, so screen readers read the new text
      button.setAttribute('aria-disabled', 'true');
      button.textContent = button.dataset.busyLabel;
    });
  });
</script>

<?php if ( $notice && $notice['type'] === 'error' ) : ?>
<script>
  // Move focus to the error so keyboard and screen reader users land on it (WCAG 3.3.1).
  document.getElementById('account-notice').focus();
</script>
<?php endif; ?>

<?php get_footer(); ?>
