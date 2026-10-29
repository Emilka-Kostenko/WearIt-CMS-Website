<?php get_header(); ?>

<main>

  <!-- ── SUSTAINABILITY HERO ── -->
  <section class="sustain-hero">
    <div class="sustain-hero__overlay" aria-hidden="true"></div>
    <div class="sustain-hero__content">
      <p class="sustain-hero__eyebrow"><?php esc_html_e( 'WearIt Responsibility', 'wearit' ); ?></p>
      <h1 class="sustain-hero__heading"><?php esc_html_e( 'Sustainability', 'wearit' ); ?></h1>
      <p class="sustain-hero__subtitle"><?php esc_html_e( 'We build pieces to last. We source materials responsibly. And when a garment reaches the end of its life, we make sure it doesn\'t end up in landfill.', 'wearit' ); ?></p>
    </div>
  </section>

  <!-- ── INITIATIVES LIST ── -->
  <section class="sustain-initiatives">

    <div class="sustain-initiatives__header">
      <p class="sustain-initiatives__eyebrow"><?php esc_html_e( 'What We\'re Doing', 'wearit' ); ?></p>
      <h2 class="sustain-initiatives__heading">
        <?php esc_html_e( 'Our', 'wearit' ); ?> <span class="sustain-initiatives__heading--accent"><?php esc_html_e( 'Initiatives', 'wearit' ); ?></span>
      </h2>
    </div>

    <?php if ( have_posts() ) : ?>

      <?php $card_index = 0; while ( have_posts() ) : the_post(); $card_index++; ?>

        <article class="initiative-card <?php echo ( $card_index % 2 === 0 ) ? 'initiative-card--reverse' : ''; ?>">

          <div class="initiative-card__image-wrap">
            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                <?php the_post_thumbnail( 'large', [ 'class' => 'initiative-card__image' ] ); ?>
              </a>
            <?php else : ?>
              <div class="initiative-card__image-placeholder"></div>
            <?php endif; ?>
          </div>

          <div class="initiative-card__body">

            <?php
            $eyebrow = get_field( 'initiative_eyebrow' );
            if ( $eyebrow ) :
            ?>
              <p class="initiative-card__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
            <?php endif; ?>

            <h3 class="initiative-card__title">
              <a href="<?php the_permalink(); ?>" class="initiative-card__title-link">
                <?php the_title(); ?>
              </a>
            </h3>

            <p class="initiative-card__excerpt"><?php the_excerpt(); ?></p>

            <a href="<?php the_permalink(); ?>" class="initiative-card__read-more">
              <?php esc_html_e( 'Read More', 'wearit' ); ?> &rarr;
            </a>

          </div>

        </article>

      <?php endwhile; ?>

    <?php else : ?>
      <p class="sustain-initiatives__empty"><?php esc_html_e( 'No initiatives yet. Check back soon.', 'wearit' ); ?></p>
    <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
