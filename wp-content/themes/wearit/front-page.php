<?php get_header(); ?>

<main>

  <!-- ═══════════════════════════════════════════════════════
       HERO SECTION
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

  <!-- ═══════════════════════════════════════════════════════
       DROPS SECTION
  ═══════════════════════════════════════════════════════ -->
  <section class="drops" id="drops">
    <div class="drops__container">
      <div class="drops__top">
        <div class="drops__text">
          <p class="drops__eyebrow">
            <?php
            $eyebrow = get_field( 'drops_eyebrow' );
            echo $eyebrow ? esc_html( $eyebrow ) : 'Latest';
            ?>
          </p>
          <h2 class="drops__heading">
            <span class="drops__heading-top">
              <?php
              $heading = get_field( 'drops_heading' );
              echo $heading ? esc_html( $heading ) : 'current';
              ?>
            </span>
            <span class="drops__heading-bottom">
              <?php
              $heading_bottom = get_field( 'drops_heading_bottom' );
              echo $heading_bottom ? esc_html( $heading_bottom ) : 'drops';
              ?>
            </span>
          </h2>
        </div>
        <div class="drops__view-all">
          <a href="/shop">
            <?php
            $view_all_lable = get_field( 'drops_view_all_label' );
            echo $view_all_lable ? esc_html( $view_all_lable ) : 'View All';
            ?>
            <span aria-hidden="true">→</span>
          </a>
        </div>
      </div>

      <div class="drops__cards">
        <div class="drops__card_grid">
          <?php if ( have_rows( 'drops_items' ) ) : ?>
            <?php while ( have_rows( 'drops_items' ) ) : the_row(); ?>

              <div class="drops__card">
                <?php $drops_image = get_sub_field( 'drops_image' ); ?>
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
                      $drops_title = get_sub_field( 'drops_title' );
                      echo $drops_title ? esc_html( $drops_title ) : 'Shadow Hoodie';
                      ?>
                    </h3>
                    <p class="card_price">
                      <?php
                      $drops_price = get_sub_field( 'drops_price' );
                      echo $drops_price ? esc_html( $drops_price ) : '$49.99';
                      ?>
                    </p>
                  </div>
                  <p class="card_description">
                    <?php
                    $drops_description = get_sub_field( 'drops_description' );
                    echo $drops_description ? esc_html( $drops_description ) : 'Heavy fleece · 4 colorways';
                    ?>
                  </p>
                  <?php if ( $drops_title ) : // cart key: see wearit_drop_cart_key() in inc/cart.php ?>
                    <button
                      type="button"
                      class="card_add_to_cart"
                      data-cart-key="<?php echo esc_attr( wearit_drop_cart_key( get_row_index(), $drops_title ) ); ?>"
                    >Add to Cart</button>
                  <?php else : ?>
                    <button type="button" class="card_add_to_cart" disabled>Add to Cart</button>
                  <?php endif; ?>
                </div>
              </div>

            <?php endwhile; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════════════════════════════════════════════════
       TESTIMONIALS SECTION
  ═══════════════════════════════════════════════════════ -->
  <section class="user__testimonials">
    <div class="testimonial__container">
      <div class="testimonial__top">
        <p class="testimonial__eyebrow">
          <?php
          $testimonial_eyebrow = get_field( 'testimonial_eyebrow' );
          echo $testimonial_eyebrow ? esc_html( $testimonial_eyebrow ) : 'From the Community';
          ?>
        </p>
        <h2 class="testimonial__heading">
          <?php
          $testimonial_heading = get_field( 'testimonial_heading' );
          echo $testimonial_heading ? esc_html( $testimonial_heading ) : 'What They\'re Saying';
          ?>
        </h2>
      </div>

      <div class="testimonial__cards">
        <div class="testimonial__card_grid">
          <?php
          $testimonials = get_field( 'testimonial__cards' );
          if ( $testimonials ) :
            foreach ( $testimonials as $post ) :
              setup_postdata( $post );
              ?>

              <div class="testimonial__card">

                <div class="card_rating">
                  <?php
                  $rating = (int) get_field( 'testimonial_rating' );
                  $rating = $rating ? $rating : 5;
                  for ( $i = 1; $i <= 5; $i++ ) :
                    $filled = $i <= $rating ? 'is-filled' : '';
                    ?>
                    <span class="star <?php echo esc_attr( $filled ); ?>">★</span>
                  <?php endfor; ?>
                </div>

                <p class="card_quote">
                  <?php
                  $quote = get_field( 'testimonial_quote' );
                  echo $quote
                    ? '&ldquo;' . esc_html( $quote ) . '&rdquo;'
                    : '&ldquo;Picked this up on the last drop and haven&rsquo;t taken it off.&rdquo;';
                  ?>
                </p>

                <div class="card_author">
                  <?php $testimonial_image = get_field( 'testimonial_image' ); ?>
                  <?php if ( $testimonial_image ) : ?>
                    <img
                      class="testimonial__image"
                      src="<?php echo esc_url( $testimonial_image['url'] ); ?>"
                      alt="<?php echo esc_attr( $testimonial_image['alt'] ); ?>"
                    />
                  <?php else : ?>
                    <img
                      class="testimonial__image"
                      src="<?php echo esc_url( get_template_directory_uri() . '/assets/testimonial-placeholder.jpg' ); ?>"
                      alt="Testimonial Image"
                    />
                  <?php endif; ?>

                  <div class="author_info">
                    <p class="author_name">
                      <?php
                      $name = get_field( 'testimonial_name' );
                      echo $name ? esc_html( $name ) : 'Jordan M.';
                      ?>
                    </p>
                    <p class="author_bought">
                      Bought:
                      <?php
                      $product = get_field( 'testimonial_product' );
                      echo $product ? esc_html( $product ) : 'Shadow Hoodie';
                      ?>
                    </p>
                  </div>
                </div>

              </div>

              <?php
            endforeach;
            wp_reset_postdata();
          endif;
          ?>
        </div>
      </div>
    </div>
  </section>

</main>

<!-- ═══════════════════════════════════════════════════════
     BLACK FRIDAY: floating button + slide-in panel
═══════════════════════════════════════════════════════ -->
<button type="button" class="bf-fab" aria-controls="bf-panel" aria-expanded="false">
  Black Friday Sale
</button>

<div class="bf-overlay" hidden></div>

<aside class="bf-panel" id="bf-panel" role="dialog" aria-modal="true" aria-label="Black Friday: The Block Bundle" aria-hidden="true">

  <button type="button" class="bf-panel__close" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>

  <div class="bf-panel__inner">

    <div class="bf-panel__content">
      <p class="bf-panel__badge">The Block Bundle · 26–29 Nov</p>

      <h2 class="bf-panel__title">
        <span>Black</span>
        <span>Friday</span>
      </h2>

      <p class="bf-panel__text">
        Buy more, save more. Build the full fit and the discount stacks
        automatically at checkout. Heavy fabrics, sharp cuts, made to last,
        direct from us to you at fair prices.
      </p>

      <div class="bf-panel__ctas">
        <a href="http://clothingwebshopcms-project.local/products/" class="bf-panel__cta bf-panel__cta--primary">Shop the Bundle</a>
        <a href="#" class="bf-panel__cta bf-panel__cta--secondary">Get VIP Early Access</a>
      </div>

      <ul class="bf-panel__schedule">
        <li><strong>Thu 26 Nov, 20:00 CET</strong> VIP vault opens. Sign up with your student email for the password.</li>
        <li><strong>Fri 27 Nov, 18:00 CET</strong> Public launch.</li>
        <li><strong>Sun 29 Nov, midnight</strong> Sale ends. Discounts removed automatically.</li>
      </ul>
    </div>

    <div class="bf-panel__discount">
      <p class="bf-panel__discount-kicker">Buy more, save more</p>

      <ul class="bf-panel__tiers">
        <li><span class="bf-panel__tier-items">2 items</span><span class="bf-panel__tier-off">10% off</span></li>
        <li><span class="bf-panel__tier-items">3 items</span><span class="bf-panel__tier-off">20% off</span></li>
        <li class="is-max"><span class="bf-panel__tier-items">4+ items</span><span class="bf-panel__tier-off">30% off</span></li>
      </ul>

      <p class="bf-panel__perk">Every order ships with a sticker pack and a numbered drop certificate.</p>
    </div>

  </div>
</aside>

<script>
(function () {
  var fab = document.querySelector('.bf-fab');
  var panel = document.getElementById('bf-panel');
  var overlay = document.querySelector('.bf-overlay');
  if (!fab || !panel || !overlay) return;

  var closeBtn = panel.querySelector('.bf-panel__close');

  function openPanel() {
    overlay.hidden = false;
    requestAnimationFrame(function () {
      overlay.classList.add('is-visible');
      panel.classList.add('is-open');
    });
    panel.setAttribute('aria-hidden', 'false');
    fab.setAttribute('aria-expanded', 'true');
    document.body.classList.add('bf-open');
    closeBtn.focus();
  }

  function closePanel() {
    overlay.classList.remove('is-visible');
    panel.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
    fab.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('bf-open');
    setTimeout(function () { overlay.hidden = true; }, 300);
    fab.focus();
  }

  fab.addEventListener('click', openPanel);
  closeBtn.addEventListener('click', closePanel);
  overlay.addEventListener('click', closePanel);
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && panel.classList.contains('is-open')) closePanel();
  });
})();
</script>

<?php get_footer(); ?>