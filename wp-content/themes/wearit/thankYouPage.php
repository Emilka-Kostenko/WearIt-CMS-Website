<?php
/**
 * Template Name: Thank You Page
 *
 * Where the customer lands after placing an order. Reads ?order= and ?key= from
 * the URL; wearit_thankyou_order() in inc/checkout.php checks the key, so order
 * numbers can't be typed in to read someone else's details.
 * Reuses the cart page's panels (.cart-summary, .cart-button, .cart-empty).
 */
get_header();

$order = wearit_thankyou_order();
?>

<main class="shop thankyou-page">

  <!-- ── HERO — SCF fields: thankyou_eyebrow, thankyou_heading, thankyou_accent ── -->
  <section class="shop-hero">
    <div class="shop-hero__container cart-hero">
      <div>
        <p class="shop-hero__eyebrow"><?php echo esc_html( wearit_checkout_text( 'thankyou_eyebrow' ) ); ?></p>
        <h1 class="shop-hero__heading">
          <?php echo esc_html( wearit_checkout_text( 'thankyou_heading' ) ); ?>
          <span class="shop-hero__heading--accent"><?php echo esc_html( wearit_checkout_text( 'thankyou_accent' ) ); ?></span>
        </h1>
      </div>
      <?php if ( $order ) : ?>
        <p class="cart-hero__count"><?php echo esc_html( $order['reference'] ); ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="shop-body">
    <div class="shop-body__container">

      <?php if ( ! $order ) : ?>

        <!-- ── no order in the URL, or the key didn't match ── -->
        <div class="cart-empty">
          <h2 class="cart-empty__title">
            <?php esc_html_e( 'Order', 'wearit' ); ?>
            <span class="cart-empty__title--accent"><?php esc_html_e( 'not found.', 'wearit' ); ?></span>
          </h2>
          <p class="cart-empty__text"><?php echo esc_html( wearit_checkout_text( 'thankyou_missing' ) ); ?></p>
          <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="cart-button cart-button--primary cart-empty__button">
            <?php echo esc_html( wearit_checkout_text( 'thankyou_button' ) ); ?>
          </a>
        </div>

      <?php else : ?>

        <!-- ── LEFT: confirmation + delivery details ── -->
        <div class="thankyou-panel">

          <p class="thankyou-panel__text"><?php echo esc_html( wearit_checkout_text( 'thankyou_text' ) ); ?></p>

          <dl class="thankyou-panel__facts">
            <div>
              <dt><?php esc_html_e( 'Order', 'wearit' ); ?></dt>
              <dd><?php echo esc_html( $order['reference'] ); ?></dd>
            </div>
            <div>
              <dt><?php esc_html_e( 'Placed', 'wearit' ); ?></dt>
              <dd><?php echo esc_html( $order['date'] ); ?></dd>
            </div>
            <div>
              <dt><?php esc_html_e( 'Payment', 'wearit' ); ?></dt>
              <dd><?php echo esc_html( wearit_payment_label( $order['payment'] ) ); ?></dd>
            </div>
          </dl>

          <h2 class="thankyou-panel__title"><?php esc_html_e( 'Delivering to', 'wearit' ); ?></h2>
          <p class="thankyou-panel__address">
            <?php echo esc_html( $order['name'] ); ?><br />
            <?php echo esc_html( $order['address'] ); ?><br />
            <?php echo esc_html( $order['postcode'] . ' ' . $order['city'] ); ?><br />
            <?php echo esc_html( $order['country'] ); ?>
          </p>

          <p class="thankyou-panel__email">
            <?php echo esc_html( sprintf( __( 'Confirmation sent to %s', 'wearit' ), $order['email'] ) ); ?>
          </p>

          <?php if ( $order['notes'] ) : ?>
            <h2 class="thankyou-panel__title">Your notes</h2>
            <p class="thankyou-panel__address"><?php echo esc_html( $order['notes'] ); ?></p>
          <?php endif; ?>

        </div>

        <!-- ── RIGHT: what was ordered, same panel as the cart summary ── -->
        <aside class="cart-summary">

          <h2 class="cart-summary__title">Your Order</h2>

          <ul class="checkout-summary__items">
            <?php foreach ( $order['items'] as $item ) : ?>
              <li class="checkout-summary__item">
                <div class="checkout-summary__info">
                  <p class="checkout-summary__name"><?php echo esc_html( $item['title'] ?? '' ); ?></p>
                  <p class="checkout-summary__meta">
                    <?php echo esc_html( $item['qty'] ?? 0 ); ?> &times; <?php echo esc_html( wearit_format_eur( $item['price'] ?? 0 ) ); ?>
                  </p>
                </div>
                <p class="checkout-summary__line"><?php echo esc_html( wearit_format_eur( $item['line_total'] ?? 0 ) ); ?></p>
              </li>
            <?php endforeach; ?>
          </ul>

          <dl class="cart-summary__rows">
            <div class="cart-summary__row">
              <dt><?php esc_html_e( 'Subtotal', 'wearit' ); ?></dt>
              <dd><?php echo esc_html( wearit_format_eur( $order['subtotal'] ) ); ?></dd>
            </div>
            <?php if ( $order['discount'] > 0 ) : ?>
              <div class="cart-summary__row cart-summary__row--discount">
                <dt><?php echo esc_html( wearit_checkout_text( 'checkout_discount_label', 'Discount' ) ); ?> (<?php echo esc_html( $order['coupon'] ); ?>)</dt>
                <dd>&minus;<?php echo esc_html( wearit_format_eur( $order['discount'] ) ); ?></dd>
              </div>
            <?php endif; ?>
            <div class="cart-summary__row">
              <dt><?php esc_html_e( 'Shipping', 'wearit' ); ?></dt>
              <dd>
                <?php if ( $order['shipping'] > 0 ) : ?>
                  <?php echo esc_html( wearit_format_eur( $order['shipping'] ) ); ?>
                <?php else : ?>
                  <span class="cart-summary__free"><?php esc_html_e( 'Free', 'wearit' ); ?></span>
                <?php endif; ?>
              </dd>
            </div>
            <?php if ( $order['fee'] > 0 ) : ?>
              <div class="cart-summary__row">
                <dt><?php echo esc_html( sprintf( __( '%s fee', 'wearit' ), wearit_payment_label( $order['payment'] ) ) ); ?></dt>
                <dd><?php echo esc_html( wearit_format_eur( $order['fee'] ) ); ?></dd>
              </div>
            <?php endif; ?>
          </dl>

          <div class="cart-summary__total">
            <p class="cart-summary__total-label"><?php esc_html_e( 'Total', 'wearit' ); ?></p>
            <div class="cart-summary__total-row">
              <span class="cart-summary__total-value"><?php echo esc_html( wearit_format_eur( $order['total'] ) ); ?></span>
              <span class="cart-summary__currency">EUR</span>
            </div>
          </div>

          <div class="cart-summary__actions">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="cart-button cart-button--primary">
              <?php echo esc_html( wearit_checkout_text( 'thankyou_button' ) ); ?>
            </a>
          </div>

          <p class="cart-summary__note"><?php esc_html_e( 'Keep this order number for any questions', 'wearit' ); ?></p>

        </aside>

      <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>