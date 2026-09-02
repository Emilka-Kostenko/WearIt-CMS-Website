<?php get_header(); ?>

<main>

  <!-- ═══════════════════════════════════════════════════════
       HERO SECTION
       SCF fields: sub_text, hero_text_top,
                   hero_text_bottom, hero_body,
                   hero_cta_primary_label, hero_cta_primary_url,
                   hero_cta_secondary_label, hero_cta_secondary_url,
                   hero_image
  ═══════════════════════════════════════════════════════ -->
  <section class="hero" id="hero">
    <div class="hero__container">

      <div class="hero__content">

        <p class="hero__eyebrow">
          <?php
          $eyebrow = get_field( 'sub_text' );
          echo $eyebrow ? esc_html( $eyebrow ) : 'New Collection SS26';
          ?>
        </p>

        <h1 class="hero__heading">
          <span class="hero__heading-line1">
            <?php
            $line1 = get_field( 'hero_text_top' );
            echo $line1 ? esc_html( $line1 ) : 'Wear It.';
            ?>
          </span>
          <span class="hero__heading-line2">
            <?php
            $line2 = get_field( 'hero_text_bottom' );
            echo $line2 ? esc_html( $line2 ) : 'Own It.';
            ?>
          </span>
        </h1>

        <p class="hero__body">
          <?php
          $body = get_field( 'hero_description' );
          echo $body ? esc_html( $body ) : 'We don\'t follow trends. Every piece is cut with purpose — heavy fabrics, sharp silhouettes, zero compromises. Made to last. Made to be seen.';
          ?>
        </p>

        <div class="hero__ctas">
          <a href="<?php echo esc_url( get_field( 'button_products_url' ) ?: '#' ); ?>" class="hero__cta hero__cta--primary">
            <?php echo esc_html( get_field( 'button_products_label' ) ?: 'Shop Products' ); ?>
          </a>
          <a href="<?php echo esc_url( get_field( 'button_blog_url' ) ?: '#' ); ?>" class="hero__cta hero__cta--secondary">
            <?php echo esc_html( get_field( 'button_blog_label' ) ?: 'Read the Blog' ); ?>
          </a>
        </div>

      </div>

      <div class="hero__image-wrap">
        <?php
        $hero_image = get_field( 'hero_image' );
        if ( $hero_image ) :
        ?>
          <img
            class="hero__image"
            src="<?php echo esc_url( $hero_image['url'] ); ?>"
            alt="<?php echo esc_attr( $hero_image['alt'] ); ?>"
          />
        <?php else : ?>
          <img
            class="hero__image"
            src="<?php echo esc_url( get_template_directory_uri() . '/assets/hero-placeholder.jpg' ); ?>"
            alt="Model in dark streetwear editorial"
          />
        <?php endif; ?>
        <div class="hero__image-fade" aria-hidden="true"></div>
      </div>

    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════
       BRAND MESSAGE SECTION
       SCF fields: brand_headline, brand_headline_accent_word,
                   brand_body
  ═══════════════════════════════════════════════════════ -->
  <section class="brand-message">
    <div class="brand-message__container">

      <h2 class="brand-message__heading">
        <?php
        $headline = get_field( 'slogan_text' );
        echo $headline ? wp_kses_post( $headline ) : '"Wear What You <span class="brand-message__accent">Stand</span> For."';
        ?>
      </h2>

      <p class="brand-message__body">
        <?php
        $brand_body = get_field( 'slogan_subtext' );
        echo $brand_body ? wp_kses_post( $brand_body ) : 'Born from the streets — not boardrooms. <span class="brand-message__accent brand-message__accent--bold">No investors. No shortcuts.</span> Just a refusal to accept how soft modern fashion has become.';
        ?>
      </p>

    </div>
  </section>

<section class="drops" id="drops">
  <div class="drops__container">
    <div class="drops__top">
      <div class="drops__text">
        <p class="drops__eyebrow">
          <?php
          $eyebrow = get_field('drops_eyebrow');
          echo $eyebrow ? esc_html($eyebrow) : 'Latest';
          ?>
        </p>
        <h2 class="drops__heading">
          <span class="drops__heading-top">
            <?php
            $heading = get_field('drops_heading');
            echo $heading ? esc_html($heading) : 'current';
            ?>
          </span>
          <span class="drops__heading-bottom">
            <?php
            $heading_bottom = get_field('drops_heading_bottom');
            echo $heading_bottom ? esc_html($heading_bottom) : 'drops';
            ?>
          </span>
        </h2>
      </div>
      <div class="drops__view-all">
        <a href="/shop">
          <?php
          $view_all_lable = get_field('drops_view_all_label');
          echo $view_all_lable ? esc_html($view_all_lable) : 'View All';
          ?>
          <span aria-hidden="true">→</span>
        </a>
      </div>
    </div>
<div class="drops__cards">
  <div class="drops__card_grid">
    <?php
    $cards = [1, 2, 3];
    foreach ( $cards as $i ) :
    ?>
    <div class="drops__card">
      <?php $drops_image = get_field( 'drops_image' ); ?>
      <?php if ( $drops_image ) : ?>
        <img
          class="drops__image"
          src="<?php echo esc_url( $drops_image['url'] ); ?>"
          alt="<?php echo esc_attr( $drops_image['alt'] ); ?>"
        />
      <?php else : ?>
        <img
          class="drops__image"
          src="<?php echo esc_url( get_template_directory_uri() . '/assets/drops-placeholder.jpg' ); ?>"
          alt="Shadow Hoodie"
        />
      <?php endif; ?>

      <div class="card_content">
        <div class="card_top">
          <h3 class="card_title">
            <?php
            $drops_title = get_field( 'drops_title' );
            echo $drops_title ? esc_html( $drops_title ) : 'Shadow Hoodie';
            ?>
          </h3>
          <p class="card_price">
            <?php
            $drops_price = get_field( 'drops_price' );
            echo $drops_price ? esc_html( $drops_price ) : '$49.99';
            ?>
          </p>
        </div>
        <p class="card_description">
          <?php
          $drops_description = get_field( 'drops_description' );
          echo $drops_description ? esc_html( $drops_description ) : 'Heavy fleece · 4 colorways';
          ?>
        </p>
        <button class="card_add_to_cart">Add to Cart</button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
</section>

</main>

<?php get_footer(); ?>
