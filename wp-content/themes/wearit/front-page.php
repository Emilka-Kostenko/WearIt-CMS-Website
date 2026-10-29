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
          echo $eyebrow ? esc_html( $eyebrow ) : esc_html__( 'New Collection SS26', 'wearit' );
          ?>
        </p>

        <h1 class="hero__heading">
          <span class="hero__heading-line1">
            <?php
            $line1 = get_field( 'hero_text_top' );
            echo $line1 ? esc_html( $line1 ) : esc_html__( 'Wear It.', 'wearit' );
            ?>
          </span>
          <span class="hero__heading-line2">
            <?php
            $line2 = get_field( 'hero_text_bottom' );
            echo $line2 ? esc_html( $line2 ) : esc_html__( 'Own It.', 'wearit' );
            ?>
          </span>
        </h1>

        <p class="hero__body">
          <?php
          $body = get_field( 'hero_description' );
          echo $body ? esc_html( $body ) : esc_html__( 'We don\'t follow trends. Every piece is cut with purpose — heavy fabrics, sharp silhouettes, zero compromises. Made to last. Made to be seen.', 'wearit' );
          ?>
        </p>

        <div class="hero__ctas">
          <a href="<?php echo esc_url( get_field( 'button_products_url' ) ?: '#' ); ?>" class="hero__cta hero__cta--primary">
            <?php echo esc_html( get_field( 'button_products_label' ) ?: __( 'Shop Products', 'wearit' ) ); ?>
          </a>
          <a href="<?php echo esc_url( get_field( 'button_blog_url' ) ?: '#' ); ?>" class="hero__cta hero__cta--secondary">
            <?php echo esc_html( get_field( 'button_blog_label' ) ?: __( 'Read the Blog', 'wearit' ) ); ?>
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
            echo $eyebrow ? esc_html( $eyebrow ) : esc_html__( 'Latest', 'wearit' );
            ?>
          </p>
          <h2 class="drops__heading">
            <span class="drops__heading-top">
              <?php
              $heading = get_field( 'drops_heading' );
              echo $heading ? esc_html( $heading ) : esc_html__( 'current', 'wearit' );
              ?>
            </span>
            <span class="drops__heading-bottom">
              <?php
              $heading_bottom = get_field( 'drops_heading_bottom' );
              echo $heading_bottom ? esc_html( $heading_bottom ) : esc_html__( 'drops', 'wearit' );
              ?>
            </span>
          </h2>
        </div>
        <div class="drops__view-all">
          <a href="/shop">
            <?php
            $view_all_lable = get_field( 'drops_view_all_label' );
            echo $view_all_lable ? esc_html( $view_all_lable ) : esc_html__( 'View All', 'wearit' );
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
                    alt="<?php esc_attr_e( 'Shadow Hoodie', 'wearit' ); ?>"
                  />
                <?php endif; ?>

                <div class="card_content">
                  <div class="card_top">
                    <h3 class="card_title">
                      <?php
                      $drops_title = get_sub_field( 'drops_title' );
                      echo $drops_title ? esc_html( $drops_title ) : esc_html__( 'Shadow Hoodie', 'wearit' );
                      ?>
                    </h3>
                    <p class="card_price">
                      <?php
                      $drops_price = get_sub_field( 'drops_price' );
                      $drops_amount = (float) preg_replace( '/[^0-9.]/', '', str_replace( ',', '.', (string) $drops_price ) );
                      echo $drops_amount > 0 ? esc_html( wearit_format_price( $drops_amount ) ) : esc_html( $drops_price ? $drops_price : wearit_format_price( 49.99 ) );
                      ?>
                    </p>
                  </div>
                  <p class="card_description">
                    <?php
                    $drops_description = get_sub_field( 'drops_description' );
                    echo $drops_description ? esc_html( $drops_description ) : esc_html__( 'Heavy fleece · 4 colorways', 'wearit' );
                    ?>
                  </p>
                  <?php if ( $drops_title ) : // cart key: see wearit_drop_cart_key() in inc/cart.php ?>
                    <button
                      type="button"
                      class="card_add_to_cart"
                      data-cart-key="<?php echo esc_attr( wearit_drop_cart_key( get_row_index(), $drops_title ) ); ?>"
                    ><?php esc_html_e( 'Add to Cart', 'wearit' ); ?></button>
                  <?php else : ?>
                    <button type="button" class="card_add_to_cart" disabled><?php esc_html_e( 'Add to Cart', 'wearit' ); ?></button>
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
          echo $testimonial_eyebrow ? esc_html( $testimonial_eyebrow ) : esc_html__( 'From the Community', 'wearit' );
          ?>
        </p>
        <h2 class="testimonial__heading">
          <?php
          $testimonial_heading = get_field( 'testimonial_heading' );
          echo $testimonial_heading ? esc_html( $testimonial_heading ) : esc_html__( 'What They\'re Saying', 'wearit' );
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
                    : '&ldquo;' . esc_html__( 'Picked this up on the last drop and haven’t taken it off.', 'wearit' ) . '&rdquo;';
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
                      alt="<?php esc_attr_e( 'Testimonial Image', 'wearit' ); ?>"
                    />
                  <?php endif; ?>

                  <div class="author_info">
                    <p class="author_name">
                      <?php
                      $name = get_field( 'testimonial_name' );
                      echo $name ? esc_html( $name ) : esc_html__( 'Jordan M.', 'wearit' );
                      ?>
                    </p>
                    <p class="author_bought">
                      <?php esc_html_e( 'Bought:', 'wearit' ); ?>
                      <?php
                      $product = get_field( 'testimonial_product' );
                      echo $product ? esc_html( $product ) : esc_html__( 'Shadow Hoodie', 'wearit' );
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
  <?php esc_html_e( 'Black Friday Sale', 'wearit' ); ?>
</button>

<div class="bf-overlay" hidden></div>

<aside class="bf-panel" id="bf-panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Black Friday: The Block Bundle', 'wearit' ); ?>" aria-hidden="true">

  <button type="button" class="bf-panel__close" aria-label="<?php esc_attr_e( 'Close', 'wearit' ); ?>">
    <span aria-hidden="true">&times;</span>
  </button>

  <div class="bf-panel__inner">

    <div class="bf-panel__content">
      <p class="bf-panel__badge"><?php esc_html_e( 'The Block Bundle · 26–29 Nov', 'wearit' ); ?></p>

      <h2 class="bf-panel__title">
        <span>Black</span>
        <span>Friday</span>
      </h2>

      <p class="bf-panel__text">
        <?php esc_html_e( 'Buy more, save more. Build the full fit and the discount stacks automatically at checkout. Heavy fabrics, sharp cuts, made to last, direct from us to you at fair prices.', 'wearit' ); ?>
      </p>

      <div class="bf-panel__ctas">
        <a href="http://clothingwebshopcms-project.local/products/" class="bf-panel__cta bf-panel__cta--primary"><?php esc_html_e( 'Shop the Bundle', 'wearit' ); ?></a>
        <button type="button" class="bf-panel__cta bf-panel__cta--secondary" data-vip-open><?php esc_html_e( 'Get VIP Early Access', 'wearit' ); ?></button>
      </div>

      <ul class="bf-panel__schedule">
        <li><strong><?php esc_html_e( 'Thu 26 Nov, 20:00 CET', 'wearit' ); ?></strong> <?php esc_html_e( 'VIP vault opens. Sign up with your student email for the password.', 'wearit' ); ?></li>
        <li><strong><?php esc_html_e( 'Fri 27 Nov, 18:00 CET', 'wearit' ); ?></strong> <?php esc_html_e( 'Public launch.', 'wearit' ); ?></li>
        <li><strong><?php esc_html_e( 'Sun 29 Nov, midnight', 'wearit' ); ?></strong> <?php esc_html_e( 'Sale ends. Discounts removed automatically.', 'wearit' ); ?></li>
      </ul>
    </div>

    <div class="bf-panel__discount">
      <p class="bf-panel__discount-kicker"><?php esc_html_e( 'Buy more, save more', 'wearit' ); ?></p>

      <ul class="bf-panel__tiers">
        <li><span class="bf-panel__tier-items"><?php esc_html_e( '2 items', 'wearit' ); ?></span><span class="bf-panel__tier-off"><?php esc_html_e( '10% off', 'wearit' ); ?></span></li>
        <li><span class="bf-panel__tier-items"><?php esc_html_e( '3 items', 'wearit' ); ?></span><span class="bf-panel__tier-off"><?php esc_html_e( '20% off', 'wearit' ); ?></span></li>
        <li class="is-max"><span class="bf-panel__tier-items"><?php esc_html_e( '4+ items', 'wearit' ); ?></span><span class="bf-panel__tier-off"><?php esc_html_e( '30% off', 'wearit' ); ?></span></li>
      </ul>

      <p class="bf-panel__perk"><?php esc_html_e( 'Every order ships with a sticker pack and a numbered drop certificate.', 'wearit' ); ?></p>
    </div>

  </div>
</aside>

<div class="vip-backdrop" hidden></div>

<div class="vip-modal" id="vip-modal" role="dialog" aria-modal="true" aria-labelledby="vip-title" aria-hidden="true" hidden>
  <button type="button" class="vip-modal__close" aria-label="<?php esc_attr_e( 'Close', 'wearit' ); ?>">&times;</button>

  <p class="vip-modal__eyebrow"><?php esc_html_e( 'VIP vault · Thu 26 Nov, 20:00 CET', 'wearit' ); ?></p>
  <h2 class="vip-modal__title" id="vip-title"><?php esc_html_e( 'Get in early', 'wearit' ); ?></h2>
  <p class="vip-modal__text">
    <?php esc_html_e( 'Drop your email and we\'ll send you the vault password before the public launch. Limited spots, first come first served.', 'wearit' ); ?>
  </p>

  <form class="vip-modal__form" novalidate>
    <input type="hidden" name="action" value="wearit_vip_signup">
    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'wearit_vip_signup' ) ); ?>">
    <!-- honeypot: real users never see or fill this -->
    <input type="text" name="website" class="vip-modal__hp" tabindex="-1" autocomplete="off" aria-hidden="true">

    <label for="vip-email"><?php esc_html_e( 'Email', 'wearit' ); ?></label>
    <input type="email" id="vip-email" name="email" class="vip-modal__input" placeholder="<?php esc_attr_e( 'you@university.dk', 'wearit' ); ?>" required autocomplete="email">

    <button type="submit" class="vip-modal__submit"><?php esc_html_e( 'Sign me up', 'wearit' ); ?></button>
    <p class="vip-modal__note"><?php esc_html_e( 'By signing up you agree to get emails about the drop. Unsubscribe any time.', 'wearit' ); ?></p>
  </form>

  <p class="vip-modal__message" role="status" aria-live="polite"></p>
</div>

<script>
(function () {
  var fab = document.querySelector('.bf-fab');
  var panel = document.getElementById('bf-panel');
  var overlay = document.querySelector('.bf-overlay');
  if (!fab || !panel || !overlay) return;

  var closeBtn = panel.querySelector('.bf-panel__close');

  /* ── Black Friday panel ── */
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

  /* ── VIP modal ── */
  var vipOpen = document.querySelector('[data-vip-open]');
  var vipModal = document.getElementById('vip-modal');
  var vipBackdrop = document.querySelector('.vip-backdrop');
  var vipForm = vipModal ? vipModal.querySelector('.vip-modal__form') : null;
  var vipMsg = vipModal ? vipModal.querySelector('.vip-modal__message') : null;
  var vipEmail = document.getElementById('vip-email');
  var vipClose = vipModal ? vipModal.querySelector('.vip-modal__close') : null;
  var vipAjaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';

  function openVip() {
    vipModal.hidden = false;
    vipBackdrop.hidden = false;
    requestAnimationFrame(function () {
      vipModal.classList.add('is-open');
      vipBackdrop.classList.add('is-visible');
    });
    vipModal.setAttribute('aria-hidden', 'false');
    vipEmail.focus();
  }

  function closeVip() {
    vipModal.classList.remove('is-open');
    vipBackdrop.classList.remove('is-visible');
    vipModal.setAttribute('aria-hidden', 'true');
    setTimeout(function () {
      vipModal.hidden = true;
      vipBackdrop.hidden = true;
    }, 250);
    if (vipOpen) vipOpen.focus();
  }

  if (vipOpen && vipModal && vipForm) {
    vipOpen.addEventListener('click', openVip);
    vipClose.addEventListener('click', closeVip);
    vipBackdrop.addEventListener('click', closeVip);

    vipForm.addEventListener('submit', function (e) {
      e.preventDefault();
      vipMsg.className = 'vip-modal__message';
      vipMsg.textContent = '';

      if (!vipEmail.value || !vipEmail.checkValidity()) {
        vipMsg.classList.add('is-error');
        vipMsg.textContent = <?php echo wp_json_encode( __( 'Please enter a valid email address.', 'wearit' ) ); ?>;
        vipEmail.focus();
        return;
      }

      var submitBtn = vipForm.querySelector('.vip-modal__submit');
      submitBtn.disabled = true;

      fetch(vipAjaxUrl, { method: 'POST', body: new FormData(vipForm) })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res.success) {
            vipForm.hidden = true;
            vipMsg.classList.add('is-success');
            vipMsg.textContent = res.data.message;
          } else {
            vipMsg.classList.add('is-error');
            vipMsg.textContent = (res && res.data && res.data.message) || <?php echo wp_json_encode( __( 'Something went wrong. Try again.', 'wearit' ) ); ?>;
            submitBtn.disabled = false;
          }
        })
        .catch(function () {
          vipMsg.classList.add('is-error');
          vipMsg.textContent = <?php echo wp_json_encode( __( 'Network error. Try again.', 'wearit' ) ); ?>;
          submitBtn.disabled = false;
        });
    });
  }

  /* ── Escape: close the modal first, then the panel ── */
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (vipModal && !vipModal.hidden) { closeVip(); return; }
    if (panel.classList.contains('is-open')) closePanel();
  });
})();
</script>

<?php get_footer(); ?>