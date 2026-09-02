<?php get_header(); ?>

<main class="single-post">
  <div class="single-post__container">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <article id="post-<?php the_ID(); ?>" <?php post_class( 'single-post__article' ); ?>>

        <!-- Post header -->
        <header class="single-post__header">
          <p class="single-post__meta">
            <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
              <?php echo esc_html( get_the_date() ); ?>
            </time>
          </p>
          <h1 class="single-post__title"><?php the_title(); ?></h1>
        </header>

        <!-- Featured image -->
        <?php if ( has_post_thumbnail() ) : ?>
          <div class="single-post__thumbnail">
            <?php the_post_thumbnail( 'full', [ 'class' => 'single-post__image' ] ); ?>
          </div>
        <?php endif; ?>

        <!-- Post content -->
        <div class="single-post__content">
          <?php the_content(); ?>
        </div>

        <!-- Navigation to prev/next post -->
        <nav class="single-post__nav" aria-label="Post navigation">
          <?php
          the_post_navigation( [
            'prev_text' => '&larr; %title',
            'next_text' => '%title &rarr;',
          ] );
          ?>
        </nav>

      </article>

      <!-- Comments — WordPress handles all logic automatically -->
      <?php comments_template(); ?>

    <?php endwhile; endif; ?>

  </div>
</main>

<?php get_footer(); ?>
