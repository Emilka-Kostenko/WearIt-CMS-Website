<?php get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

  <!-- Breadcrumbs -->
  <nav class="post-breadcrumbs" aria-label="Breadcrumb">
    <div class="site-container">
      <a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
      <span class="separator">/</span>
      <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>">Blog</a>
      <span class="separator">/</span>
      <span class="current"><?php the_title(); ?></span>
    </div>
  </nav>

  <!-- Hero Section (70vh) -->
  <?php 
    $thumb_url = has_post_thumbnail() 
      ? get_the_post_thumbnail_url( get_the_ID(), 'full' ) 
      : ''; 
    $categories = get_the_category();
  ?>
  <section class="post-hero" style="<?php echo $thumb_url ? 'background-image: url(' . esc_url( $thumb_url ) . ');' : ''; ?>">
    <div class="hero-overlay"></div>
    <div class="site-container hero-content">
      <?php if ( ! empty( $categories ) ) : ?>
        <span class="topic-badge"><?php echo esc_html( $categories[0]->name ); ?></span>
      <?php endif; ?>
      <h1 class="post-hero-title"><?php the_title(); ?></h1>
    </div>
  </section>

  <!-- Post Body & Collapsible Area -->
  <article class="post-body-wrap">
    <div class="site-container post-container-narrow">
      <div class="entry-content-collapsible" id="post-content-area">
        <?php the_content(); ?>
      </div>

      <button id="toggle-read-more" class="btn-read-more" aria-expanded="false">
        Read More &darr;
      </button>
    </div>
  </article>

  <!-- Discussion Section -->
  <section class="discussion-section">
    <div class="site-container post-container-narrow">
      <h2 class="section-title">Discussion</h2>

      <!-- Success alert bar on submission -->
      <?php if ( isset( $_GET['comment_status'] ) && $_GET['comment_status'] === 'submitted' ) : ?>
        <div class="comment-alert-bar">
          Comment posted successfully!
        </div>
      <?php endif; ?>

      <!-- Real WordPress Comments Thread -->
      <div class="comments-thread" id="comments-thread">
        <?php
        $comments = get_comments( array(
            'post_id' => get_the_ID(),
            'status'  => 'approve',
            'order'   => 'ASC',
        ) );

        if ( ! empty( $comments ) ) :
            foreach ( $comments as $comment ) :
                $author_name = $comment->comment_author;
                $words = explode( ' ', trim( $author_name ) );
                $initials = '';
                foreach ( array_slice( $words, 0, 2 ) as $w ) {
                    $initials .= strtoupper( substr( $w, 0, 1 ) );
                }
                ?>
                <div class="comment-item" id="comment-<?php echo esc_attr( $comment->comment_ID ); ?>">
                  <div class="comment-avatar"><?php echo esc_html( $initials ? $initials : '?' ); ?></div>
                  <div class="comment-body">
                    <div class="comment-meta">
                      <strong class="comment-author"><?php echo esc_html( $author_name ); ?></strong>
                      <span class="comment-date"><?php echo esc_html( human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) . ' ago' ); ?></span>
                    </div>
                    <p class="comment-text"><?php echo nl2br( esc_html( $comment->comment_content ) ); ?></p>
                  </div>
                </div>
            <?php endforeach;
        else : ?>
            <p class="no-comments">No comments yet. Be the first to join the conversation.</p>
        <?php endif; ?>
      </div>

      <!-- Real WordPress Comment Form -->
      <form action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" method="post" id="discussion-form" class="discussion-form">
        <h3>Join the Conversation</h3>

        <?php if ( ! is_user_logged_in() ) : ?>
          <div class="form-group">
            <label for="author">Name</label>
            <input type="text" name="author" id="author" required placeholder="Your name">
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required placeholder="Your email (kept private)">
          </div>
        <?php else : ?>
          <p class="logged-in-meta">
            Logged in as <strong><?php echo esc_html( wp_get_current_user()->display_name ); ?></strong>.
          </p>
        <?php endif; ?>

        <div class="form-group">
          <label for="comment">Comment</label>
          <textarea name="comment" id="comment" rows="4" required placeholder="Add your take..."></textarea>
        </div>

        <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr( get_the_ID() ); ?>" id="comment_post_ID">
        <input type="hidden" name="comment_parent" id="comment_parent" value="0">

        <button type="submit" class="btn-submit-comment">Post Comment</button>
      </form>

      <!-- Bottom Nav Link -->
      <div class="bottom-nav">
        <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="back-link">
          &larr; Back to All Articles
        </a>
      </div>
    </div>
  </section>

  <!-- Collapsible Toggle Script -->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const readMoreBtn = document.getElementById('toggle-read-more');
    const postContent = document.getElementById('post-content-area');

    if (readMoreBtn && postContent) {
      readMoreBtn.addEventListener('click', function () {
        const isExpanded = postContent.classList.toggle('expanded');
        readMoreBtn.setAttribute('aria-expanded', isExpanded);
        readMoreBtn.innerHTML = isExpanded ? 'Show Less &uarr;' : 'Read More &darr;';
      });
    }
  });
  </script>

<?php endwhile; endif; ?>

<?php get_footer(); ?>