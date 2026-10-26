<?php
/**
 * One product card. Used inside the loop on archive-product.php.
 * Data comes from wearit_get_product() — see inc/products.php.
 */
$product = wearit_get_product( get_the_ID() );
if ( ! $product ) {
    return;
}
?>
<article class="product-card" id="product-<?php echo esc_attr( $product['id'] ); ?>">

  <div class="product-card__media">
    <?php if ( $product['image'] ) : ?>
      <img
        class="product-card__image"
        src="<?php echo esc_url( $product['image'] ); ?>"
        alt="<?php echo esc_attr( $product['title'] ); ?>"
        loading="lazy"
      />
    <?php else : ?>
      <div class="product-card__placeholder" role="img" aria-label="<?php echo esc_attr( $product['title'] ); ?>">
        <span><?php echo esc_html( $product['category_label'] ); ?></span>
      </div>
    <?php endif; ?>

    <?php if ( $product['is_new'] ) : ?>
      <span class="product-card__badge">New</span>
    <?php endif; ?>
  </div>

  <div class="product-card__body">

    <div class="product-card__row">
      <div class="product-card__info">
        <h2 class="product-card__title"><?php echo esc_html( $product['title'] ); ?></h2>
        <p class="product-card__meta">
          <span class="product-card__dot" style="--swatch: <?php echo esc_attr( $product['color_hex'] ); ?>" aria-hidden="true"></span>
          <?php echo esc_html( $product['color_label'] . ' · ' . $product['category_label'] ); ?>
        </p>
      </div>
      <p class="product-card__price"><?php echo esc_html( $product['price_formatted'] ); ?></p>
    </div>

    <?php if ( $product['size_range'] ) : ?>
      <p class="product-card__sizes">Sizes <?php echo esc_html( $product['size_range'] ); ?></p>
    <?php endif; ?>

    <!--
      Interface only — no cart logic yet.
      The cart developer reads these data-* attributes (or just data-product-id
      and calls wearit_get_product() on the server).
    -->
    <button
      type="button"
      class="product-card__add"
      data-product-id="<?php echo esc_attr( $product['id'] ); ?>"
      data-sku="<?php echo esc_attr( $product['sku'] ); ?>"
      data-price="<?php echo esc_attr( $product['price'] ); ?>"
      <?php disabled( ! $product['in_stock'] ); ?>
    >
      <?php echo $product['in_stock'] ? 'Add to Cart' : 'Sold out'; ?>
    </button>

  </div>
</article>
