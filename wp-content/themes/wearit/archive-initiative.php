<?php get_header(); ?>

<main>

  <!-- ── SUSTAINABILITY HERO ── -->
  <section class="sustain-hero">
    <div class="sustain-hero__overlay" aria-hidden="true"></div>
    <div class="sustain-hero__content">
      <p class="sustain-hero__eyebrow">WearIt Responsibility</p>
      <h1 class="sustain-hero__heading">Sustainability</h1>
      <p class="sustain-hero__subtitle">We build pieces to last. We source materials responsibly. And when a garment reaches the end of its life, we make sure it doesn't end up in landfill.</p>
    </div>
  </section>

  <!-- ── INITIATIVES LIST ── -->
  <section class="sustain-initiatives">

    <div class="sustain-initiatives__header">
      <p class="sustain-initiatives__eyebrow">What We're Doing</p>
      <h2 class="sustain-initiatives__heading">
        Our <span class="sustain-initiatives__heading--accent">Initiatives</span>
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
              Read More &rarr;
            </a>

          </div>

        </article>

      <?php endwhile; ?>

    <?php else : ?>
      <p class="sustain-initiatives__empty">No initiatives yet. Check back soon.</p>
    <?php endif; ?>

    </div>
  </section>

</main>

<?php get_footer(); ?>
