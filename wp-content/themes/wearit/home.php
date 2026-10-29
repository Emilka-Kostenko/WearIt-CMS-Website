<?php get_header(); ?>

<main>

  <!-- ── BLOG HERO ──────────────────────────────────────────
       SCF fields (Group: "Blog Page Hero", Location: Page = Blog):
         blog_hero_image    → Image (return format: Image Array)
         blog_hero_eyebrow  → Text
         blog_hero_heading  → Text
         blog_hero_subtitle → Textarea
  ──────────────────────────────────────────────────────── -->
  <?php
  $hero_image = get_field( 'blog_hero_image', get_option( 'page_for_posts' ) );
  $hero_bg    = $hero_image ? 'background-image: url(' . esc_url( $hero_image['url'] ) . ');' : '';
  ?>
  <section class="blog-hero" style="<?php echo $hero_bg; ?>">
    <div class="blog-hero__overlay" aria-hidden="true"></div>
    <div class="blog-hero__content">

      <p class="blog-hero__eyebrow">
        <?php
        $eyebrow = get_field( 'blog_hero_eyebrow', get_option( 'page_for_posts' ) );
        echo $eyebrow ? esc_html( $eyebrow ) : esc_html__( 'WearIt Editorial', 'wearit' );
        ?>
      </p>

      <h1 class="blog-hero__heading">
        <?php
        $heading = get_field( 'blog_hero_heading', get_option( 'page_for_posts' ) );
        echo $heading ? esc_html( $heading ) : esc_html__( 'The Blog', 'wearit' );
        ?>
      </h1>

      <p class="blog-hero__subtitle">
        <?php
        $subtitle = get_field( 'blog_hero_subtitle', get_option( 'page_for_posts' ) );
        echo $subtitle ? esc_html( $subtitle ) : esc_html__( 'Stories about fabric, form, and everything in between.', 'wearit' );
        ?>
      </p>

    </div>
  </section>

  <!-- ── CATEGORIES DROPDOWN ── -->
  <div class="cat-filter">
    <div class="cat-filter__container">
      <div class="cat-filter__dropdown" id="cat-dropdown">
        <button class="cat-filter__toggle" id="cat-toggle" aria-expanded="false" aria-controls="cat-menu">
          <?php
          $current_cat = get_queried_object();
          echo isset( $current_cat->name ) ? esc_html( mb_strtoupper( $current_cat->name ) ) : esc_html( mb_strtoupper( __( 'Categories', 'wearit' ) ) );
          ?>
          <span class="cat-filter__arrow" aria-hidden="true">&#8963;</span>
        </button>
        <ul class="cat-filter__menu" id="cat-menu" role="listbox">
          <li>
            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"
               class="cat-filter__option <?php echo ! is_category() ? 'is-active' : ''; ?>">
              <?php esc_html_e( 'All', 'wearit' ); ?>
            </a>
          </li>
          <?php
          $cats = get_categories( [ 'hide_empty' => true ] );
          foreach ( $cats as $cat ) :
          ?>
          <li>
            <a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"
               class="cat-filter__option">
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

  <!-- ── BLOG ARCHIVE ── -->
  <div class="blog-archive">
  <div class="blog-archive__container">

    <!-- ── LATEST heading + post count ── -->
    <div class="blog-archive__header">
      <h1 class="blog-archive__title"><?php esc_html_e( 'Latest', 'wearit' ); ?></h1>
      <?php if ( $wp_query->found_posts ) : ?>
        <span class="blog-archive__count">
          <?php echo esc_html( sprintf( _n( '%d article', '%d articles', (int) $wp_query->found_posts, 'wearit' ), $wp_query->found_posts ) ); ?>
        </span>
      <?php endif; ?>
    </div>

    <!-- ── Post card grid ── -->
    <?php if ( have_posts() ) : ?>

      <div class="post-card-grid">
        <?php while ( have_posts() ) : the_post(); ?>

          <article class="post-card" id="post-<?php the_ID(); ?>">

            <!-- Image -->
            <div class="post-card__image-wrap">
              <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
                  <?php the_post_thumbnail( 'large', [ 'class' => 'post-card__image' ] ); ?>
                </a>
              <?php else : ?>
                <div class="post-card__image-placeholder"></div>
              <?php endif; ?>
            </div>

            <!-- Card body -->
            <div class="post-card__body">

              <!-- Title -->
              <h3 class="post-card__title">
                <a href="<?php the_permalink(); ?>" class="post-card__title-link">
                  <?php the_title(); ?>
                </a>
              </h3>

              <!-- Meta: category + author + date -->
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

              <!-- Excerpt -->
              <p class="post-card__excerpt"><?php the_excerpt(); ?></p>

              <!-- Read more -->
              <a href="<?php the_permalink(); ?>" class="post-card__read-more">
                <?php esc_html_e( 'Read More', 'wearit' ); ?> &rarr;
              </a>

            </div>
          </article>

        <?php endwhile; ?>
      </div>

      <!-- Pagination -->
      <nav class="blog-pagination" aria-label="<?php esc_attr_e( 'Blog pages', 'wearit' ); ?>">
        <?php
        the_posts_pagination( [
          'prev_text' => '&larr; Newer',
          'next_text' => 'Older &rarr;',
        ] );
        ?>
      </nav>

    <?php else : ?>
      <p class="blog-archive__empty"><?php esc_html_e( 'No posts yet. Check back soon.', 'wearit' ); ?></p>
    <?php endif; ?>

  </div>
  </div><!-- /.blog-archive -->

</main>

<?php get_footer(); ?>
