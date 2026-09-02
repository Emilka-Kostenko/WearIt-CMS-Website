<?php get_header(); ?>

<main>

  <!-- ── CATEGORY HERO ── -->
  <section class="blog-hero">
    <div class="blog-hero__overlay" aria-hidden="true"></div>
    <div class="blog-hero__content">
      <p class="blog-hero__eyebrow">WearIt Editorial</p>
      <h1 class="blog-hero__heading"><?php single_cat_title(); ?></h1>
      <p class="blog-hero__subtitle">
        <?php
        $desc = category_description();
        echo $desc ? wp_kses_post( $desc ) : 'Browsing articles in this category.';
        ?>
      </p>
    </div>
  </section>

  <!-- ── CATEGORIES DROPDOWN ── -->
  <div class="cat-filter">
    <div class="cat-filter__container">
      <div class="cat-filter__dropdown" id="cat-dropdown">
        <button class="cat-filter__toggle" id="cat-toggle" aria-expanded="false" aria-controls="cat-menu">
          <?php single_cat_title(); ?>
          <span class="cat-filter__arrow" aria-hidden="true">&#8963;</span>
        </button>
        <ul class="cat-filter__menu" id="cat-menu" role="listbox">
          <li>
            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"
               class="cat-filter__option">
              All
            </a>
          </li>
          <?php
          $cats = get_categories( [ 'hide_empty' => true ] );
          foreach ( $cats as $cat ) :
          ?>
          <li>
            <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
               class="cat-filter__option <?php echo ( get_queried_object_id() === $cat->term_id ) ? 'is-active' : ''; ?>">
              <?php echo esc_html( $cat->name ); ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <script>
    (function () {
      const toggle = document.getElementById('cat-toggle');
      const menu   = document.getElementById('cat-menu');
      const wrap   = document.getElementById('cat-dropdown');
      toggle.addEventListener('click', function () {
        const open = wrap.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', open);
      });
      document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) {
          wrap.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
    })();
  </script>

  <!-- ── CATEGORY ARCHIVE ── -->
  <div class="blog-archive">
  <div class="blog-archive__container">

    <div class="blog-archive__header">
      <h2 class="blog-archive__title"><?php single_cat_title(); ?></h2>
      <?php if ( $wp_query->found_posts ) : ?>
        <span class="blog-archive__count">
          <?php echo esc_html( $wp_query->found_posts ); ?> articles
        </span>
      <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>

      <div class="post-card-grid">
        <?php while ( have_posts() ) : the_post(); ?>

          <article class="post-card" id="post-<?php the_ID(); ?>">

            <div class="post-card__image-wrap">
              <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                  <?php the_post_thumbnail( 'large', [ 'class' => 'post-card__image' ] ); ?>
                </a>
              <?php else : ?>
                <div class="post-card__image-placeholder"></div>
              <?php endif; ?>
            </div>

            <div class="post-card__body">

              <h3 class="post-card__title">
                <a href="<?php the_permalink(); ?>" class="post-card__title-link">
                  <?php the_title(); ?>
                </a>
              </h3>

              <div class="post-card__meta">
                <?php
                $categories = get_the_category();
                if ( $categories ) :
                  foreach ( $categories as $cat ) :
                ?>
                  <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
                     class="post-card__category">
                    <?php echo esc_html( $cat->name ); ?>
                  </a>
                <?php
                  endforeach;
                endif;
                ?>

                <?php
                $author_name = get_field( 'post_author_name' );
                if ( $author_name ) :
                ?>
                  <span class="post-card__author"><?php echo esc_html( $author_name ); ?></span>
                <?php endif; ?>

                <time class="post-card__date"
                      datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
                  <?php echo esc_html( get_the_date( 'j M Y' ) ); ?>
                </time>
              </div>

              <p class="post-card__excerpt"><?php the_excerpt(); ?></p>

              <a href="<?php the_permalink(); ?>" class="post-card__read-more">
                Read More &rarr;
              </a>

            </div>
          </article>

        <?php endwhile; ?>
      </div>

      <nav class="blog-pagination" aria-label="Category pages">
        <?php
        the_posts_pagination( [
          'prev_text' => '&larr; Newer',
          'next_text' => 'Older &rarr;',
        ] );
        ?>
      </nav>

    <?php else : ?>
      <p class="blog-archive__empty">No posts in this category yet.</p>
    <?php endif; ?>

  </div>
  </div>

</main>

<?php get_footer(); ?>
