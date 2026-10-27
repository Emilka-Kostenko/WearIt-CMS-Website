<?php
/**
 * Hero slide 2: Black Week. All text comes from the admin
 * (Pages → Home → "Hero slider — Black Week slide"); see wearit_get_hero_slider().
 *
 * Expects $args['slider'] from get_template_part().
 */
$s = $args['slider'];

// Each word of the heading goes on its own line, like in the design.
$heading_words = array_filter( preg_split( '/\s+/', trim( $s['heading'] ) ) );
?>
<div class="hero-slider__slide black-week" role="group" aria-roledescription="slide" aria-label="2 of 2">
  <div class="black-week__container">

    <div class="black-week__content">
      <?php if ( $s['tag'] ) : ?>
        <p class="black-week__tag"><?php echo esc_html( $s['tag'] ); ?></p>
      <?php endif; ?>

      <h2 class="black-week__heading">
        <?php echo implode( '<br>', array_map( 'esc_html', $heading_words ) ); ?>
      </h2>

      <?php if ( $s['text'] ) : ?>
        <p class="black-week__text"><?php echo esc_html( $s['text'] ); ?></p>
      <?php endif; ?>

      <div class="black-week__ctas">
        <a href="<?php echo esc_url( $s['primary_url'] ); ?>" class="black-week__cta black-week__cta--primary">
          <?php echo esc_html( $s['primary_label'] ); ?>
        </a>
        <a href="<?php echo esc_url( $s['secondary_url'] ); ?>" class="black-week__cta black-week__cta--secondary">
          <?php echo esc_html( $s['secondary_label'] ); ?>
        </a>
      </div>
    </div>

    <div class="black-week__discount" aria-label="<?php echo esc_attr( $s['discount'] . '% ' . $s['discount_label'] ); ?>">
      <p class="black-week__number" aria-hidden="true"><?php echo esc_html( $s['discount'] ); ?><span class="black-week__percent">%</span></p>
      <p class="black-week__discount-label" aria-hidden="true"><?php echo esc_html( $s['discount_label'] ); ?></p>
    </div>

  </div>
</div>
