<?php
/**
 * Mini-cart panel shown under the bag icon in the header.
 * Rendered in header.php and re-rendered by the cart REST endpoints (inc/cart.php).
 */
$cart = wearit_get_cart();
?>
<div class="mini-cart__header">
  <p class="mini-cart__title">Your Cart</p>
  <p class="mini-cart__count">
    <?php echo esc_html( $cart['count'] ); ?> item<?php echo $cart['count'] === 1 ? '' : 's'; ?>
  </p>
</div>

<?php if ( $cart['items'] ) : ?>

  <ul class="mini-cart__items">
    <?php foreach ( $cart['items'] as $item ) : ?>
      <li class="mini-cart__item">
        <a href="<?php echo esc_url( $item['permalink'] ); ?>" class="mini-cart__thumb">
          <?php if ( $item['image'] ) : ?>
            <img src="<?php echo esc_url( $item['image'] ); ?>" alt="" loading="lazy" />
          <?php endif; ?>
        </a>

        <div class="mini-cart__info">
          <a href="<?php echo esc_url( $item['permalink'] ); ?>" class="mini-cart__name">
            <?php echo esc_html( $item['title'] ); ?>
          </a>
          <p class="mini-cart__meta">
            <?php echo esc_html( ( $item['meta'] ? $item['meta'] . ' · ' : '' ) . 'Qty ' . $item['qty'] ); ?>
          </p>
        </div>

        <div class="mini-cart__side">
          <p class="mini-cart__price"><?php echo esc_html( wearit_format_price( $item['line_total'] ) ); ?></p>
          <button
            type="button"
            class="mini-cart__remove"
            data-cart-key="<?php echo esc_attr( $item['key'] ); ?>"
            aria-label="<?php echo esc_attr( 'Remove ' . $item['title'] ); ?>"
          >Remove</button>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="mini-cart__footer">
    <p class="mini-cart__subtotal">
      <span>Subtotal</span>
      <span class="mini-cart__subtotal-value"><?php echo esc_html( $cart['subtotal_formatted'] ); ?></span>
    </p>
    <a href="<?php echo esc_url( wearit_cart_url() ); ?>" class="mini-cart__button">Go to Cart</a>
  </div>

<?php else : ?>

  <div class="mini-cart__empty">
    <p>Your cart is empty.</p>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="mini-cart__button mini-cart__button--ghost">Shop Products</a>
  </div>

<?php endif; ?>
