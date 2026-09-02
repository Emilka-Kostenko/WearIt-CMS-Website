<?php get_header(); ?>

<main class="page-content">
  <div class="page-content__container">

    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

      <article id="page-<?php the_ID(); ?>" <?php post_class( 'page-content__article' ); ?>>
        <h1 class="page-content__title"><?php the_title(); ?></h1>
        <div class="page-content__body">
          <?php the_content(); ?>
        </div>
      </article>

    <?php endwhile; endif; ?>

  </div>
</main>

<?php get_footer(); ?>
