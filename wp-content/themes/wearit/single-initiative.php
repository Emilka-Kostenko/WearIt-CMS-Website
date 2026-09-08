<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<?php
$thumb_url = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) : '';
$eyebrow   = get_field( 'initiative_eyebrow' );
$quote     = get_field( 'initiative_quote' );
$sec_image = get_field( 'initiative_secondary_image' );
?>

  <!-- ── HERO IMAGE ── -->
  <section class="initiative-hero" style="<?php echo $thumb_url ? 'background-image: url(' . esc_url( $thumb_url ) . ');' : ''; ?>">
    <div class="initiative-hero__overlay" aria-hidden="true"></div>
  </section>

  <!-- ── CONTENT ── -->
  <article class="initiative-single">
    <div class="initiative-single__container">

      <!-- Eyebrow + Title -->
      <?php if ( $eyebrow ) : ?>
        <p class="initiative-single__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
      <?php endif; ?>

      <h1 class="initiative-single__title"><?php the_title(); ?></h1>

      <!-- Body content from WordPress editor -->
      <div class="initiative-single__content">
        <?php the_content(); ?>
      </div>

      <!-- Pull quote -->
      <?php if ( $quote ) : ?>
        <blockquote class="initiative-single__quote">
          <?php echo esc_html( $quote ); ?>
        </blockquote>
      <?php endif; ?>

      <!-- Secondary image -->
      <?php if ( $sec_image ) : ?>
        <div class="initiative-single__secondary-image">
          <img
            src="<?php echo esc_url( $sec_image['url'] ); ?>"
            alt="<?php echo esc_attr( $sec_image['alt'] ); ?>"
          />
        </div>
      <?php endif; ?>

      <!-- Back link -->
      <div class="initiative-single__back">
        <a href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>" class="initiative-single__back-link">
          &larr; Back to Sustainability
        </a>
      </div>

    </div>
  </article>

<?php endwhile; endif; ?>

<?php get_footer(); ?>
