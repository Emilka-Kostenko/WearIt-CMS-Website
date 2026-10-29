<?php

get_header();

$cart   = wearit_get_cart();
$totals = wearit_cart_totals( $cart );
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
        <?php echo esc_html( $cart['count'] ); ?> item<?php echo $cart['count'] === 1 ? '' : 's'; ?>
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
              <p class="checkout-fields__error">Some details were missing or not valid. Please check the fields below.</p>
            <?php elseif ( isset( $_GET['checkout'] ) && $_GET['checkout'] === 'empty' ) : ?>
              <p class="checkout-fields__error">Your cart was empty when the order was sent.</p>
            <?php elseif ( isset( $_GET['checkout'] ) && $_GET['checkout'] === 'error' ) : ?>
              <p class="checkout-fields__error">Something went wrong saving your order. Please try again.</p>
            <?php endif; ?>

            <div class="checkout-fields__row"> <!-- name and email side by side -->
              <div class="checkout-field">
                <label for="checkout_name"><?php echo esc_html( wearit_checkout_text( 'checkout_name_label' ) ); ?></label>
                <input type="text" id="checkout_name" name="checkout_name" class="checkout-field__input" placeholder="Your name" value="<?php echo esc_attr( wp_get_current_user()->display_name ); ?>" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_email"><?php echo esc_html( wearit_checkout_text( 'checkout_email_label' ) ); ?></label>
                <input type="email" id="checkout_email" name="checkout_email" class="checkout-field__input" placeholder="you@email.com" value="<?php echo esc_attr( wp_get_current_user()->user_email ); ?>" required />
              </div>
            </div>

            <div class="checkout-field">
              <label for="checkout_address"><?php echo esc_html( wearit_checkout_text( 'checkout_address_label' ) ); ?></label>
              <input type="text" id="checkout_address" name="checkout_address" class="checkout-field__input" placeholder="Street and number" required />
            </div>

            <div class="checkout-fields__row checkout-fields__row--three">
              <div class="checkout-field">
                <label for="checkout_postcode"><?php echo esc_html( wearit_checkout_text( 'checkout_post_label' ) ); ?></label>
                <input type="text" id="checkout_postcode" name="checkout_postcode" class="checkout-field__input" placeholder="0000" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_city"><?php echo esc_html( wearit_checkout_text( 'checkout_city_label' ) ); ?></label>
                <input type="text" id="checkout_city" name="checkout_city" class="checkout-field__input" placeholder="City" required />
              </div>
              <div class="checkout-field">
                <label for="checkout_country"><?php echo esc_html( wearit_checkout_text( 'checkout_country_label' ) ); ?></label>
                <input type="text" id="checkout_country" name="checkout_country" class="checkout-field__input" placeholder="Country" required />
              </div>
            </div>

            <div class="checkout-field">
              <label for="checkout_notes"><?php echo esc_html( wearit_checkout_text( 'checkout_notes_label' ) ); ?></label>
              <textarea id="checkout_notes" name="checkout_notes" class="checkout-field__input checkout-field__input--area" rows="4" placeholder="Anything we should know about the delivery?"></textarea>
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

            <dl class="cart-summary__rows">
              <div class="cart-summary__row">
                <dt>Subtotal</dt>
                <dd><?php echo esc_html( wearit_cart_format_price( $cart['subtotal'] ) ); ?></dd>
              </div>
              <div class="cart-summary__row">
                <dt>Shipping</dt>
                <dd>
                  <?php if ( $totals['shipping'] > 0 ) : ?>
                    <?php echo esc_html( wearit_cart_format_price( $totals['shipping'] ) ); ?>
                  <?php else : ?>
                    <span class="cart-summary__free">Free</span>
                  <?php endif; ?>
                </dd>
              </div>
            </dl>

            <div class="cart-summary__total">
              <p class="cart-summary__total-label">Total</p>
              <div class="cart-summary__total-row">
                <span class="cart-summary__total-value"><?php echo esc_html( wearit_cart_format_price( $totals['total'] ) ); ?></span>
                <span class="cart-summary__currency">USD</span>
              </div>
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

      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>