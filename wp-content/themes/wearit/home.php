<?php get_header(); ?>

<main class="blog-archive">
  <div class="blog-archive__container">

    <h1 class="blog-archive__title">Blog</h1>

    <?php if ( have_posts() ) : ?>

      <div class="blog-archive__grid">
        <?php while ( have_posts() ) : the_post(); ?>

          <article class="post-card" id="post-<?php the_ID(); ?>">

            <?php if ( has_post_thumbnail() ) : ?>
              <a href="<?php the_permalink(); ?>" class="post-card__image-wrap" tabindex="-1" aria-hidden="true">
                <?php the_post_thumbnail( 'large', [ 'class' => 'post-card__image' ] ); ?>
              </a>
            <?php endif; ?>

            <div class="post-card__body">
              <p class="post-card__meta">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                  <?php echo esc_html( get_the_date() ); ?>
                </time>
              </p>
              <h2 class="post-card__title">
                <a href="<?php the_permalink(); ?>" class="post-card__link">
                  <?php the_title(); ?>
                </a>
              </h2>
              <p class="post-card__excerpt"><?php the_excerpt(); ?></p>
              <a href="<?php the_permalink(); ?>" class="post-card__cta">Read more</a>
            </div>

          </article>

        <?php endwhile; ?>
      </div>

      <!-- Pagination -->
      <nav class="blog-pagination" aria-label="Blog pages">
        <?php
        the_posts_pagination( [
          'prev_text' => '&larr; Newer',
          'next_text' => 'Older &rarr;',
        ] );
        ?>
      </nav>

    <?php else : ?>

      <p class="blog-archive__empty">No posts yet. Check back soon.</p>

    <?php endif; ?>

  </div>
</main>

<?php get_footer(); ?>
