<?php
/**
 * Shop page — all products, with category and color filters.
 * Served at /products/. Filters are plain links; see wearit_filter_url() in inc/products.php.
 */
get_header();

$filters    = wearit_get_active_filters();
$categories = get_terms( [
    'taxonomy'   => 'product_category',
    'hide_empty' => false,
    'orderby'    => 'term_id', // creation order: Jackets, Hoodies, … as in the design
] );
$colors     = wearit_product_colors();
$colors_in_use = wearit_colors_in_use();
$found     = (int) $GLOBALS['wp_query']->found_posts;
?>

<main class="shop">

  <!-- ── HERO ── -->
  <section class="shop-hero">
    <div class="shop-hero__container">
      <p class="shop-hero__eyebrow"><?php esc_html_e( 'Available now', 'wearit' ); ?></p>
      <h1 class="shop-hero__heading"><?php esc_html_e( 'All', 'wearit' ); ?> <span class="shop-hero__heading--accent"><?php esc_html_e( 'Products', 'wearit' ); ?></span></h1>
    </div>
  </section>

  <section class="shop-body">
    <div class="shop-body__container">

      <!-- ── FILTERS ── -->
      <aside class="shop-filters" aria-label="<?php esc_attr_e( 'Product filters', 'wearit' ); ?>">

        <div class="shop-filters__header">
          <h2 class="shop-filters__title"><?php esc_html_e( 'Filters', 'wearit' ); ?></h2>
          <?php if ( wearit_has_active_filters() ) : ?>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="shop-filters__clear"><?php esc_html_e( 'Clear filters', 'wearit' ); ?></a>
          <?php else : ?>
            <span class="shop-filters__clear shop-filters__clear--disabled"><?php esc_html_e( 'Clear filters', 'wearit' ); ?></span>
          <?php endif; ?>
        </div>

        <h3 class="shop-filters__group-title"><?php esc_html_e( 'Categories', 'wearit' ); ?></h3>
        <ul class="shop-filters__list">
          <?php if ( $categories && ! is_wp_error( $categories ) ) : foreach ( $categories as $cat ) :
            $active = in_array( $cat->slug, $filters['product_cat'], true );
          ?>
            <li>
              <a
                href="<?php echo esc_url( wearit_filter_url( 'product_cat', $cat->slug ) ); ?>"
                class="shop-filters__option<?php echo $active ? ' is-active' : ''; ?>"
                aria-pressed="<?php echo $active ? 'true' : 'false'; ?>"
              >
                <span class="shop-filters__box" aria-hidden="true"></span>
                <?php echo esc_html( $cat->name ); ?>
              </a>
            </li>
          <?php endforeach; endif; ?>
        </ul>

        <h3 class="shop-filters__group-title shop-filters__group-title--spaced"><?php esc_html_e( 'Color', 'wearit' ); ?></h3>
        <ul class="shop-filters__swatches">
          <?php foreach ( $colors as $slug => $color ) :
            $active = in_array( $slug, $filters['color'], true );
            // Hide colors no product uses (an already-selected one stays so it can be switched off).
            if ( ! $active && ! in_array( $slug, $colors_in_use, true ) ) {
                continue;
            }
          ?>
            <li>
              <a
                href="<?php echo esc_url( wearit_filter_url( 'color', $slug ) ); ?>"
                class="shop-filters__swatch<?php echo $active ? ' is-active' : ''; ?>"
                style="--swatch: <?php echo esc_attr( $color['hex'] ); ?>"
                aria-label="<?php echo esc_attr( $color['label'] ); ?>"
                aria-pressed="<?php echo $active ? 'true' : 'false'; ?>"
                title="<?php echo esc_attr( $color['label'] ); ?>"
              ></a>
            </li>
          <?php endforeach; ?>
        </ul>

      </aside>

      <!-- ── PRODUCT GRID ── -->
      <div class="shop-results">

        <p class="shop-results__count">
          <?php echo esc_html( sprintf( $found === 1 ? __( 'Showing %d product', 'wearit' ) : __( 'Showing %d products', 'wearit' ), $found ) ); ?>
        </p>

        <?php if ( have_posts() ) : ?>
          <div class="product-grid">
            <?php while ( have_posts() ) : the_post(); ?>
              <?php get_template_part( 'template-parts/product-card' ); ?>
            <?php endwhile; ?>
          </div>
        <?php else : ?>
          <div class="shop-results__empty">
            <p class="shop-results__empty-title"><?php esc_html_e( 'No products match these filters', 'wearit' ); ?></p>
            <a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="shop-results__empty-link"><?php esc_html_e( 'Clear filters', 'wearit' ); ?></a>
          </div>
        <?php endif; ?>

      </div>

    </div>
  </section>

</main>

<?php get_footer(); ?>
