<?php

get_header();

$cart          = wearit_get_cart();
$totals        = wearit_checkout_totals( $cart ); // includes the applied coupon (inc/coupons.php)
$coupon_notice = wearit_coupon_notice();
?>

<main class="shop checkout-page">

  <!-- ── HERO — SCF fields: checkout_eyebrow, checkout_heading, checkout_heading_accent ── -->
  <section class="shop-hero">
    <div class="shop-hero__container cart-hero">
      <div>
        <p class="shop-hero__eyebrow"><?php echo esc_html( wearit_checkout_text( 'checkout_eyebrow' ) ); ?></p>
        <h1 class="shop-hero__heading">
          <?php echo esc_html( wearit_checkout_text( 'checkout_heading' ) ); ?>
          <span class="shop-hero__heading--accent"><?php echo esc_html( wearit_checkout_text( 'checkout_heading_accent' ) ); ?></span>
        </h1>
      </div>
      <p class="cart-hero__count">
        <?php echo esc_html( sprintf( $cart['count'] === 1 ? __( '%d item', 'wearit' ) : __( '%d items', 'wearit' ), $cart['count'] ) ); ?>
      </p>
    </div>
  </section>

  <section class="shop-body">
    <div class="shop-body__container">

      <?php if ( ! $cart['items'] ) : ?>

        <!-- ── EMPTY CART — same look as the cart page's empty state ── -->
        <div class="cart-empty">
          <h2 class="cart-empty__title">
            <?php echo esc_html( wearit_checkout_text( 'checkout_empty_heading' ) ); ?>
            <span class="cart-empty__title--accent"><?php echo esc_html( wearit_checkout_text( 'checkout_empty_accent' ) ); ?></span>
          </h2>
          <p class="cart-empty__text"><?php echo esc_html( wearit_checkout_text( 'checkout_empty_text' ) ); ?></p>
          <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="cart-button cart-button--primary cart-empty__button">
            <?php echo esc_html( wearit_checkout_text( 'checkout_empty_button' ) ); ?>
          </a>
        </div>

      <?php elseif ( ! is_user_logged_in() ) : ?>

        <!-- ── NOT LOGGED IN — checkout needs an account (inc/account.php) ── -->
        <?php get_template_part( 'template-parts/login-required', null, [
          'context'  => 'checkout',
          'title'    => 'Log in to check out',
          'text'     => 'You need a WearIt account to place an order. Your cart stays as it is while you log in or sign up.',
          'redirect' => wearit_checkout_url(),
        ] ); ?>

      <?php else : ?>

        <!-- the form wraps both columns so the button in the summary can submit it -->
        <form class="checkout-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
          <input type="hidden" name="action" value="wearit_checkout" />
          <?php wp_nonce_field( 'wearit_checkout_nonce', 'wearit_checkout_nonce' ); ?>

          <!-- ── DELIVERY DETAILS ── -->
          <div class="checkout-fields">

            <h2 class="checkout-fields__title"><?php echo esc_html( wearit_checkout_text( 'checkout_form_title' ) ); ?></h2>

            <?php // came back here with ?checkout=… so something went wrong ?>
            <?php if ( isset( $_GET['checkout'] ) && $_GET['checkout'] === 'invalid' ) : ?>
              <p class="checkout-fields__error"><?php esc_html_e( 'Some details were missing or not valid. Please check the fields below.', 'wearit' ); ?></p>
            <?php elseif ( isset( $_GET['checkout'] ) && $_GET['checkout'] === 'empty' ) : ?>
              <p class="checkout-fields__error"><?php esc_html_e( 'Your cart was empty when the order was sent.', 'wearit' ); ?></p>
            <?php elseif ( isset( $_GET['checkout'] ) && $_GET['checkout'] === 'error' ) : ?>
              <p class="checkout-fields__error"><?php esc_html_e( 'Something went wrong saving your order. Please try again.', 'wearit' ); ?></p>
            <?php endif; ?>

            <div class="checkout-fields__row"> <!-- name and email side by side -->
              <div class="checkout-field">
                <label for="checkout_name"><?php echo esc_html( wearit_checkout_text( 'checkout_name_label' ) ); ?></label>
                <input type="text" id="checkout_name" name="checkout_name" class="checkout-field__input" placeholder="<?php esc_attr_e( 'Your name', 'wearit' ); ?>" value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_email"><?php echo esc_html( wearit_checkout_text( 'checkout_email_label' ) ); ?></label>
                <input type="email" id="checkout_email" name="checkout_email" class="checkout-field__input" placeholder="<?php esc_attr_e( 'you@email.com', 'wearit' ); ?>" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" required />
              </div>
            </div>

            <div class="checkout-field">
              <label for="checkout_address"><?php echo esc_html( wearit_checkout_text( 'checkout_address_label' ) ); ?></label>
              <input type="text" id="checkout_address" name="checkout_address" class="checkout-field__input" placeholder="<?php esc_attr_e( 'Street and number', 'wearit' ); ?>" required />
            </div>

            <div class="checkout-fields__row checkout-fields__row--three">
              <div class="checkout-field">
                <label for="checkout_postcode"><?php echo esc_html( wearit_checkout_text( 'checkout_post_label' ) ); ?></label>
                <input type="text" id="checkout_postcode" name="checkout_postcode" class="checkout-field__input" placeholder="0000" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_city"><?php echo esc_html( wearit_checkout_text( 'checkout_city_label' ) ); ?></label>
                <input type="text" id="checkout_city" name="checkout_city" class="checkout-field__input" placeholder="<?php esc_attr_e( 'City', 'wearit' ); ?>" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_country"><?php echo esc_html( wearit_checkout_text( 'checkout_country_label' ) ); ?></label>
                <input type="text" id="checkout_country" name="checkout_country" class="checkout-field__input" placeholder="<?php esc_attr_e( 'Country', 'wearit' ); ?>" required />
              </div>
            </div>

            <div class="checkout-field">
              <label for="checkout_notes"><?php echo esc_html( wearit_checkout_text( 'checkout_notes_label' ) ); ?></label>
              <textarea id="checkout_notes" name="checkout_notes" class="checkout-field__input checkout-field__input--area" rows="4" placeholder="<?php esc_attr_e( 'Anything we should know about the delivery?', 'wearit' ); ?>"></textarea>
            </div>

            <!-- ── PAYMENT  ── -->
            <h2 class="checkout-fields__title checkout-fields__title--sub"><?php echo esc_html( wearit_checkout_text( 'checkout_payment_label' ) ); ?></h2>

            <div class="checkout-payment">
              <?php $first = true; foreach ( wearit_enabled_payment_methods() as $key => $method ) : ?>
                <label class="checkout-payment__option" for="payment_<?php echo esc_attr( $key ); ?>">
                  <input
                    type="radio"
                    id="payment_<?php echo esc_attr( $key ); ?>"
                    name="checkout_payment"
                    value="<?php echo esc_attr( $key ); ?>"
                    <?php checked( $first ); ?>
                    required
                  />
                  <span class="checkout-payment__text">
                    <span class="checkout-payment__label">
                      <?php echo esc_html( $method['label'] ); ?>
                      <?php if ( $method['fee'] > 0 ) : ?>
                        <span class="checkout-payment__fee">+<?php echo esc_html( wearit_cart_format_price( $method['fee'] ) ); ?></span>
                      <?php endif; ?>
                    </span>
                    <span class="checkout-payment__description"><?php echo esc_html( $method['description'] ); ?></span>
                  </span>
                </label>
              <?php $first = false; endforeach; ?>
            </div>

            <?php // Stripe's embedded card form, shown by assets/js/checkout-stripe.js when "Card" is picked ?>
            <?php if ( isset( wearit_enabled_payment_methods()['card'] ) ) : ?>
              <div id="checkout-form" hidden></div>
            <?php endif; ?>

          </div>

          <!-- ── ORDER SUMMARY the cart page's summary panel, plus the item list ── -->
          <aside class="cart-summary">

            <h2 class="cart-summary__title"><?php echo esc_html( wearit_checkout_text( 'checkout_summary_title' ) ); ?></h2>

            <ul class="checkout-summary__items">
              <?php foreach ( $cart['items'] as $item ) : ?>
                <li class="checkout-summary__item">
                  <div class="checkout-summary__thumb">
                    <?php if ( $item['image'] ) : ?>
                      <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" />
                    <?php endif; ?>
                  </div>
                  <div class="checkout-summary__info">
                    <p class="checkout-summary__name"><?php echo esc_html( $item['title'] ); ?></p>
                    <p class="checkout-summary__meta"><?php echo esc_html( $item['qty'] ); ?> &times; <?php echo esc_html( wearit_cart_format_price( $item['price'] ) ); ?></p>
                  </div>
                  <p class="checkout-summary__line"><?php echo esc_html( wearit_cart_format_price( $item['line_total'] ) ); ?></p>
                </li>
              <?php endforeach; ?>
            </ul>

            <!-- ── COUPON — its inputs belong to the small forms after this form (form="…"),
                 so Enter in the field applies the code instead of placing the order ── -->
            <div class="checkout-coupon" id="checkout-coupon">
              <?php if ( $totals['coupon'] ) : ?>
                <p class="checkout-coupon__applied">
                  <span><?php echo str_replace(
                    [ '{code}', '{discount}' ],
                    [ '<strong>' . esc_html( $totals['coupon'] ) . '</strong>', esc_html( wearit_coupons()[ $totals['coupon'] ]['label'] ) ],
                    esc_html( wearit_checkout_text( 'checkout_coupon_applied', 'Code {code} applied: {discount}' ) )
                  ); ?></span>
                  <button type="submit" form="checkout-coupon-remove" class="checkout-coupon__remove"><?php echo esc_html( wearit_checkout_text( 'checkout_coupon_remove', 'Remove' ) ); ?><span class="screen-reader-text"> <?php echo esc_html( $totals['coupon'] ); ?></span></button>
                </p>
              <?php else : ?>
                <label for="checkout_coupon" class="checkout-coupon__label"><?php echo esc_html( wearit_checkout_text( 'checkout_coupon_label', 'Coupon code' ) ); ?></label>
                <div class="checkout-coupon__row">
                  <input
                    type="text"
                    id="checkout_coupon"
                    name="coupon_code"
                    form="checkout-coupon-form"
                    class="checkout-field__input checkout-coupon__input"
                    autocomplete="off"
                    autocapitalize="characters"
                    spellcheck="false"
                    <?php if ( $coupon_notice && $coupon_notice['type'] === 'error' ) : ?>aria-invalid="true" aria-describedby="checkout-coupon-notice"<?php endif; ?>
                  />
                  <button type="submit" form="checkout-coupon-form" class="cart-button cart-button--ghost checkout-coupon__apply"><?php echo esc_html( wearit_checkout_text( 'checkout_coupon_button', 'Apply' ) ); ?></button>
                </div>
              <?php endif; ?>

              <?php if ( $coupon_notice ) : ?>
                <p
                  class="checkout-coupon__notice checkout-coupon__notice--<?php echo esc_attr( $coupon_notice['type'] ); ?>"
                  id="checkout-coupon-notice"
                  tabindex="-1"
                  role="<?php echo $coupon_notice['type'] === 'error' ? 'alert' : 'status'; ?>"
                >
                  <?php if ( $coupon_notice['type'] === 'error' ) : ?><strong><?php echo esc_html( wearit_checkout_text( 'checkout_coupon_error_label', 'Error:' ) ); ?></strong> <?php endif; ?>
                  <?php echo esc_html( $coupon_notice['text'] ); ?>
                </p>
              <?php endif; ?>
            </div>

            <dl class="cart-summary__rows">
              <div class="cart-summary__row">
                <dt><?php esc_html_e( 'Subtotal', 'wearit' ); ?></dt>
                <dd><?php echo esc_html( wearit_cart_format_price( $cart['subtotal'] ) ); ?></dd>
              </div>
              <?php if ( $totals['discount'] > 0 ) : ?>
                <div class="cart-summary__row cart-summary__row--discount">
                  <dt><?php echo esc_html( wearit_checkout_text( 'checkout_discount_label', 'Discount' ) ); ?> (<?php echo esc_html( $totals['coupon'] ); ?>)</dt>
                  <dd>&minus;<?php echo esc_html( wearit_cart_format_price( $totals['discount'] ) ); ?></dd>
                </div>
              <?php endif; ?>
              <div class="cart-summary__row">
                <dt><?php esc_html_e( 'Shipping', 'wearit' ); ?></dt>
                <dd>
                  <?php if ( $totals['shipping'] > 0 ) : ?>
                    <?php echo esc_html( wearit_cart_format_price( $totals['shipping'] ) ); ?>
                  <?php else : ?>
                    <span class="cart-summary__free"><?php esc_html_e( 'Free', 'wearit' ); ?></span>
                  <?php endif; ?>
                </dd>
              </div>
            </dl>

            <div class="cart-summary__total">
              <p class="cart-summary__total-label"><?php esc_html_e( 'Total', 'wearit' ); ?></p>
              <div class="cart-summary__total-row">
                <span class="cart-summary__total-value"><?php echo esc_html( wearit_cart_format_price( $totals['total'] ) ); ?></span>
                <span class="cart-summary__currency"><?php echo esc_html( wearit_display_currency() ); ?></span>
              </div>
              <?php if ( wearit_display_currency() !== 'EUR' ) : ?>
                <p class="cart-summary__charge-note"><?php echo esc_html( sprintf( __( 'You pay in euros: %s. Kroner are shown as a guide.', 'wearit' ), wearit_format_eur( $totals['total'] ) ) ); ?></p>
              <?php endif; ?>
            </div>

            <div class="cart-summary__actions">
              <button type="submit" class="cart-button cart-button--primary">
                <?php echo esc_html( wearit_checkout_text( 'checkout_submit_label' ) ); ?>
              </button>
              <a href="<?php echo esc_url( wearit_cart_url() ); ?>" class="cart-button cart-button--ghost">
                <?php echo esc_html( wearit_checkout_text( 'checkout_back_label' ) ); ?>
              </a>
            </div>

            <p class="cart-summary__note"><?php echo esc_html( wearit_checkout_text( 'checkout_payment_note' ) ); ?></p>

          </aside>

        </form>

        <!-- Coupon forms: no visible markup of their own, the inputs and buttons
             in the order summary point at them with form="…" (inc/coupons.php) -->
        <form id="checkout-coupon-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" hidden>
          <input type="hidden" name="action" value="wearit_apply_coupon" />
          <input type="hidden" name="wearit_coupon_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wearit_coupon_nonce' ) ); ?>" /> <!-- no wp_nonce_field(): its fixed id would repeat -->
        </form>
        <form id="checkout-coupon-remove" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" hidden>
          <input type="hidden" name="action" value="wearit_remove_coupon" />
          <input type="hidden" name="wearit_coupon_nonce" value="<?php echo esc_attr( wp_create_nonce( 'wearit_coupon_nonce' ) ); ?>" />
        </form>

        <?php if ( $coupon_notice && $coupon_notice['type'] === 'error' ) : ?>
          <script>
            // Move focus to the coupon error so keyboard and screen reader users land on it (WCAG 3.3.1).
            document.getElementById('checkout-coupon-notice').focus();
          </script>
        <?php endif; ?>

      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>