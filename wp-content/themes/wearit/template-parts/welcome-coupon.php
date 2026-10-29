<?php
/**
 * Welcome popup with the sign-up coupon. Printed once in the footer right after
 * a new account is created (inc/coupons.php).
 *
 * A native <dialog> opened with showModal(): the browser keeps keyboard focus inside it,
 * Esc closes it and the page behind is inert — the WCAG 2.2 AA basics for a modal.
 *
 * Args: code — the coupon code to show.
 */
$code     = $args['code'] ?? 'WEARIT10';
$user     = wp_get_current_user();
$has_cart = function_exists( 'wearit_get_cart' ) && wearit_get_cart()['items'];
$replace  = [ '{name}' => $user->display_name, '{code}' => $code ];
?>
<dialog class="welcome-coupon" id="welcome-coupon" aria-labelledby="welcome-coupon-title" aria-describedby="welcome-coupon-text">
  <button type="button" class="welcome-coupon__close" data-welcome-close aria-label="<?php echo esc_attr( wearit_account_text( 'welcome_close_label', 'Close' ) ); ?>">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
      <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M6 6l12 12M18 6L6 18" />
    </svg>
  </button>

  <p class="welcome-coupon__eyebrow"><?php echo esc_html( wearit_account_text( 'welcome_eyebrow', 'Welcome to WearIt' ) ); ?></p>
  <h2 class="welcome-coupon__title" id="welcome-coupon-title"><?php echo esc_html( wearit_account_text( 'welcome_heading', '10% off' ) ); ?> <span class="welcome-coupon__title--accent"><?php echo esc_html( wearit_account_text( 'welcome_heading_accent', 'your order' ) ); ?></span></h2>
  <p class="welcome-coupon__text" id="welcome-coupon-text">
    <?php echo esc_html( wearit_account_text( 'welcome_text', 'Thanks for joining, {name}. Enter this code at checkout to take 10% off. It works once.', $replace ) ); ?>
  </p>

  <div class="welcome-coupon__code-row">
    <p class="welcome-coupon__code" id="welcome-coupon-code"><?php echo esc_html( $code ); ?></p>
    <button
      type="button"
      class="welcome-coupon__copy"
      data-welcome-copy
      data-copied-label="<?php echo esc_attr( wearit_account_text( 'welcome_copied_button', 'Copied' ) ); ?>"
      data-copied-status="<?php echo esc_attr( wearit_account_text( 'welcome_copied_status', 'Copied {code} to your clipboard.', $replace ) ); ?>"
      data-failed-status="<?php echo esc_attr( wearit_account_text( 'welcome_copy_failed', 'Could not copy automatically. Select the code {code} and copy it.', $replace ) ); ?>"
      aria-describedby="welcome-coupon-code"
    ><?php echo esc_html( wearit_account_text( 'welcome_copy_button', 'Copy code' ) ); ?></button>
  </div>
  <!-- says "Copied" out loud for screen readers -->
  <p class="welcome-coupon__status" role="status" aria-live="polite" data-welcome-status></p>

  <div class="welcome-coupon__actions">
    <?php if ( $has_cart ) : ?>
      <a href="<?php echo esc_url( wearit_checkout_url() ); ?>" class="welcome-coupon__button welcome-coupon__button--primary"><?php echo esc_html( wearit_account_text( 'welcome_checkout_button', 'Go to Checkout' ) ); ?></a>
    <?php else : ?>
      <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="welcome-coupon__button welcome-coupon__button--primary"><?php echo esc_html( wearit_account_text( 'welcome_shop_button', 'Start Shopping' ) ); ?></a>
    <?php endif; ?>
    <button type="button" class="welcome-coupon__button" data-welcome-close><?php echo esc_html( wearit_account_text( 'welcome_later_button', 'Maybe later' ) ); ?></button>
  </div>
</dialog>

<script>
  (function () {
    const dialog = document.getElementById('welcome-coupon');
    if (!dialog || typeof dialog.showModal !== 'function') return;

    dialog.showModal();

    // Close: the X, "Maybe later", or a click on the dark backdrop around the box (Esc is built in)
    dialog.querySelectorAll('[data-welcome-close]').forEach((b) => b.addEventListener('click', () => dialog.close()));
    dialog.addEventListener('click', (e) => {
      if (e.target === dialog) dialog.close();
    });

    // Copy the code, and confirm it in text (not only by changing the button).
    // navigator.clipboard only exists on HTTPS (and localhost), so on http:// — like the
    // local site — fall back to selecting a hidden textarea and execCommand('copy').
    // The textarea goes inside the dialog: everything outside an open modal is inert.
    const copy = dialog.querySelector('[data-welcome-copy]');
    const status = dialog.querySelector('[data-welcome-status]');

    function copyWithTextarea(text) {
      const area = document.createElement('textarea');
      area.value = text;
      area.setAttribute('readonly', '');
      area.setAttribute('aria-hidden', 'true');
      area.style.position = 'fixed';
      area.style.opacity = '0';
      dialog.appendChild(area);
      area.select();
      let ok = false;
      try {
        ok = document.execCommand('copy');
      } catch (err) {
        ok = false;
      }
      area.remove();
      copy.focus(); // selecting the textarea moved focus; put it back on the button
      return ok;
    }

    copy.addEventListener('click', async () => {
      const code = document.getElementById('welcome-coupon-code').textContent.trim();
      let ok = false;
      if (navigator.clipboard && window.isSecureContext) {
        try {
          await navigator.clipboard.writeText(code);
          ok = true;
        } catch (err) {
          ok = false;
        }
      }
      if (!ok) {
        ok = copyWithTextarea(code);
      }
      status.textContent = ok ? copy.dataset.copiedStatus : copy.dataset.failedStatus;
      if (ok) {
        copy.textContent = copy.dataset.copiedLabel;
      }
    });
  })();
</script>
