<?php
/**
 * Cart page body: the items (or the empty message) and the order summary.
 * Rendered in cartPage.php and re-rendered by the cart REST endpoints when they get page: true (inc/cart.php).
 */
$cart     = wearit_get_cart();
$totals   = wearit_cart_totals( $cart );
$shop_url = get_post_type_archive_link( 'product' );
$checkout = get_page_by_path( 'checkout' ); // no checkout page yet; the button links there once it exists
?>

<!-- ── ITEMS ── -->
<div class="cart-items">

  <?php if ( $cart['items'] ) : ?>

    <ul class="cart-items__list">
      <?php foreach ( $cart['items'] as $item ) : ?>
        <li class="cart-item">

          <a href="<?php echo esc_url( $item['permalink'] ); ?>" class="cart-item__thumb" tabindex="-1" aria-hidden="true">
            <?php if ( $item['image'] ) : ?>
              <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy" />
            <?php endif; ?>
          </a>

          <div class="cart-item__info">
            <a href="<?php echo esc_url( $item['permalink'] ); ?>" class="cart-item__name">
              <?php echo esc_html( $item['title'] ); ?>
            </a>
            <?php if ( $item['meta'] ) : ?>
              <p class="cart-item__meta"><?php echo esc_html( $item['meta'] ); ?></p>
            <?php endif; ?>
            <p class="cart-item__unit"><?php echo esc_html( wearit_cart_format_price( $item['price'] ) ); ?> each</p>
          </div>

          <div class="cart-item__qty" role="group" aria-label="<?php echo esc_attr( sprintf( __( 'Quantity of %s', 'wearit' ), $item['title'] ) ); ?>">
            <button
              type="button"
              class="cart-item__step"
              data-cart-key="<?php echo esc_attr( $item['key'] ); ?>"
              data-qty="<?php echo esc_attr( $item['qty'] - 1 ); ?>"
              aria-label="<?php echo esc_attr( $item['qty'] === 1 ? sprintf( __( 'Remove %s', 'wearit' ), $item['title'] ) : __( 'One less', 'wearit' ) ); ?>"
            >&minus;</button>
            <span class="cart-item__qty-value"><?php echo esc_html( $item['qty'] ); ?></span>
            <button
              type="button"
              class="cart-item__step"
              data-cart-key="<?php echo esc_attr( $item['key'] ); ?>"
              data-qty="<?php echo esc_attr( $item['qty'] + 1 ); ?>"
              aria-label="<?php esc_attr_e( 'One more', 'wearit' ); ?>"
              <?php disabled( $item['qty'] >= WEARIT_CART_MAX_QTY ); ?>
            >+</button>
          </div>

          <div class="cart-item__side">
            <p class="cart-item__total"><?php echo esc_html( wearit_cart_format_price( $item['line_total'] ) ); ?></p>
            <button
              type="button"
              class="cart-item__remove"
              data-cart-key="<?php echo esc_attr( $item['key'] ); ?>"
              data-qty="0"
              aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s', 'wearit' ), $item['title'] ) ); ?>"
            >Remove</button>
          </div>

        </li>
      <?php endforeach; ?>
    </ul>

  <?php else : ?>

    <!-- SCF fields: cart_empty_heading, cart_empty_accent, cart_empty_text, cart_empty_button -->
    <div class="cart-empty">
      <h2 class="cart-empty__title">
        <?php echo esc_html( wearit_cart_text( 'cart_empty_heading' ) ); ?>
        <span class="cart-empty__title--accent"><?php echo esc_html( wearit_cart_text( 'cart_empty_accent' ) ); ?></span>
      </h2>
      <p class="cart-empty__text"><?php echo esc_html( wearit_cart_text( 'cart_empty_text' ) ); ?></p>
      <a href="<?php echo esc_url( $shop_url ); ?>" class="cart-button cart-button--primary cart-empty__button">
        <?php echo esc_html( wearit_cart_text( 'cart_empty_button' ) ); ?>
      </a>
    </div>

  <?php endif; ?>

</div>

<!-- ── ORDER SUMMARY — SCF fields: cart_summary_title, cart_checkout_label, cart_back_label, cart_tax_note, cart_shipping_price, cart_free_shipping_from ── -->
<aside class="cart-summary" aria-label="<?php esc_attr_e( 'Order summary', 'wearit' ); ?>">

  <h2 class="cart-summary__title"><?php echo esc_html( wearit_cart_text( 'cart_summary_title' ) ); ?></h2>

  <dl class="cart-summary__rows">
    <div class="cart-summary__row">
      <dt><?php esc_html_e( 'Subtotal', 'wearit' ); ?></dt>
      <dd><?php echo esc_html( wearit_cart_format_price( $cart['subtotal'] ) ); ?></dd>
    </div>
    <div class="cart-summary__row">
      <dt><?php esc_html_e( 'Shipping', 'wearit' ); ?></dt>
      <?php if ( $totals['shipping'] > 0 ) : ?>
        <dd><?php echo esc_html( wearit_cart_format_price( $totals['shipping'] ) ); ?></dd>
      <?php else : ?>
        <dd class="cart-summary__free"><?php esc_html_e( 'Free', 'wearit' ); ?></dd>
      <?php endif; ?>
    </div>
  </dl>

  <div class="cart-summary__total">
    <p class="cart-summary__total-label"><?php esc_html_e( 'Total', 'wearit' ); ?></p>
    <p class="cart-summary__total-row">
      <span class="cart-summary__total-value"><?php echo esc_html( wearit_cart_format_price( $totals['total'] ) ); ?></span>
      <span class="cart-summary__currency"><?php echo esc_html( wearit_display_currency() ); ?></span>
    </p>
  </div>

  <div class="cart-summary__actions">
    <?php if ( $cart['items'] ) : ?>
      <a href="<?php echo esc_url( $checkout ? get_permalink( $checkout ) : '#' ); ?>" class="cart-button cart-button--primary">
        <?php echo esc_html( wearit_cart_text( 'cart_checkout_label' ) ); ?>
      </a>
    <?php else : ?>
      <span class="cart-button cart-button--primary is-disabled" aria-disabled="true">
        <?php echo esc_html( wearit_cart_text( 'cart_checkout_label' ) ); ?>
      </span>
    <?php endif; ?>

    <a href="<?php echo esc_url( $shop_url ); ?>" class="cart-button cart-button--ghost">
      <?php echo esc_html( wearit_cart_text( 'cart_back_label' ) ); ?>
    </a>
  </div>

  <p class="cart-summary__note"><?php echo esc_html( wearit_cart_text( 'cart_tax_note' ) ); ?></p>

</aside>
