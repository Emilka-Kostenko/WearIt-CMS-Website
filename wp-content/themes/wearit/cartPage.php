<?php
/**
 * Template Name: Cart Page
 *
 * Full cart. Same layout as the shop page (archive-product.php): hero, then items + sidebar.
 * Texts are SCF fields on this page (registered in inc/cart.php, read with wearit_cart_text()).
 * The body is template-parts/cart-page.php, which assets/js/cart.js re-renders after − / + / Remove.
 */
get_header();

$cart = wearit_get_cart();
?>

<main class="shop cart-page">

  <!-- ── HERO — SCF fields: cart_eyebrow, cart_heading, cart_heading_accent ── -->
  <section class="shop-hero">
    <div class="shop-hero__container cart-hero">
      <div>
        <p class="shop-hero__eyebrow"><?php echo esc_html( wearit_cart_text( 'cart_eyebrow' ) ); ?></p>
        <h1 class="shop-hero__heading">
          <?php echo esc_html( wearit_cart_text( 'cart_heading' ) ); ?>
          <span class="shop-hero__heading--accent"><?php echo esc_html( wearit_cart_text( 'cart_heading_accent' ) ); ?></span>
        </h1>
      </div>
      <p class="cart-hero__count" id="cart-page-count">
        <?php echo esc_html( sprintf( $cart['count'] === 1 ? __( '%d item', 'wearit' ) : __( '%d items', 'wearit' ), $cart['count'] ) ); ?>
      </p>
    </div>
  </section>

  <!-- ── ITEMS + ORDER SUMMARY ── -->
  <section class="shop-body">
    <div class="shop-body__container" id="cart-page">
      <?php get_template_part( 'template-parts/cart-page' ); ?>
    </div>
  </section>

</main>

<?php get_footer(); ?>
