<?php
/**
 * Mini-cart panel shown under the bag icon in the header.
 * Rendered in header.php and re-rendered by the cart REST endpoints (inc/cart.php).
 */
$cart = wearit_get_cart();
?>
<div class="mini-cart__header">
  <p class="mini-cart__title"><?php echo esc_html( wearit_cart_text( 'minicart_title', 'Your Cart' ) ); ?></p>
  <p class="mini-cart__count">
    <?php
    $count_text = $cart['count'] === 1 ? wearit_cart_text( 'minicart_count_one', '{count} item' ) : wearit_cart_text( 'minicart_count_many', '{count} items' );
    echo esc_html( strtr( $count_text, [ '{count}' => $cart['count'] ] ) );
    ?>
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
            <?php echo esc_html( ( $item['meta'] ? $item['meta'] . ' · ' : '' ) . wearit_cart_text( 'minicart_qty', 'Qty' ) . ' ' . $item['qty'] ); ?>
          </p>
        </div>

        <div class="mini-cart__side">
          <p class="mini-cart__price"><?php echo esc_html( wearit_format_price( $item['line_total'] ) ); ?></p>
          <button
            type="button"
            class="mini-cart__remove"
            data-cart-key="<?php echo esc_attr( $item['key'] ); ?>"
            aria-label="<?php echo esc_attr( wearit_cart_text( 'minicart_remove', 'Remove' ) . ' ' . $item['title'] ); ?>"
          ><?php echo esc_html( wearit_cart_text( 'minicart_remove', 'Remove' ) ); ?></button>
        </div>
      </li>
    <?php endforeach; ?>
  </ul>

  <div class="mini-cart__footer">
    <p class="mini-cart__subtotal">
      <span><?php echo esc_html( wearit_cart_text( 'minicart_subtotal', 'Subtotal' ) ); ?></span>
      <span class="mini-cart__subtotal-value"><?php echo esc_html( $cart['subtotal_formatted'] ); ?></span>
    </p>
    <a href="<?php echo esc_url( wearit_cart_url() ); ?>" class="mini-cart__button"><?php echo esc_html( wearit_cart_text( 'minicart_button', 'Go to Cart' ) ); ?></a>
  </div>

<?php else : ?>

  <div class="mini-cart__empty">
    <p><?php echo esc_html( wearit_cart_text( 'minicart_empty', 'Your cart is empty.' ) ); ?></p>
    <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="mini-cart__button mini-cart__button--ghost"><?php echo esc_html( wearit_cart_text( 'minicart_empty_button', 'Shop Products' ) ); ?></a>
  </div>

<?php endif; ?>
