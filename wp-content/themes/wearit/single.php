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

  <!-- Hero: 70vh with Gradient Overlay -->
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

  <!-- Post Body & Collapsible Content -->
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

  <!-- Discussion Section (Directly below post body) -->
  <section class="discussion-section">
    <div class="site-container post-container-narrow">
      <h2 class="section-title">Discussion</h2>

      <div id="comment-confirmation" class="comment-alert-bar" style="display: none;">
        Comment posted successfully!
      </div>

      <div class="comments-thread" id="comments-thread">
        <!-- Pre-seeded mock comment 1 -->
        <div class="comment-item">
          <div class="comment-avatar">JD</div>
          <div class="comment-body">
            <div class="comment-meta">
              <strong class="comment-author">Jordan D.</strong>
              <span class="comment-date">2 days ago</span>
            </div>
            <p class="comment-text">The silhouette and proportions shown here are unreal. Need this look immediately.</p>
          </div>
        </div>

        <!-- Pre-seeded mock comment 2 -->
        <div class="comment-item">
          <div class="comment-avatar">MK</div>
          <div class="comment-body">
            <div class="comment-meta">
              <strong class="comment-author">Marcus K.</strong>
              <span class="comment-date">Yesterday</span>
            </div>
            <p class="comment-text">Great styling advice. The contrast stitching on that jacket is a clean touch.</p>
          </div>
        </div>
      </div>

      <!-- Join Conversation Form -->
      <form id="discussion-form" class="discussion-form">
        <h3>Join the Conversation</h3>
        <div class="form-group">
          <label for="comment-author-name">Name</label>
          <input type="text" id="comment-author-name" required placeholder="Your name">
        </div>
        <div class="form-group">
          <label for="comment-text-input">Comment</label>
          <textarea id="comment-text-input" rows="4" required placeholder="Add your take..."></textarea>
        </div>
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

  <!-- JavaScript for Toggle & Discussion Form -->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    // 1. Read More Toggle Logic
    const readMoreBtn = document.getElementById('toggle-read-more');
    const postContent = document.getElementById('post-content-area');

    if (readMoreBtn && postContent) {
      readMoreBtn.addEventListener('click', function () {
        const isExpanded = postContent.classList.toggle('expanded');
        readMoreBtn.setAttribute('aria-expanded', isExpanded);
        readMoreBtn.innerHTML = isExpanded ? 'Show Less &uarr;' : 'Read More &darr;';
      });
    }

    // 2. Discussion Live Submit
    const commentForm = document.getElementById('discussion-form');
    const commentList = document.getElementById('comments-thread');
    const alertBar = document.getElementById('comment-confirmation');

    if (commentForm) {
      commentForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const nameVal = document.getElementById('comment-author-name').value.trim();
        const textVal = document.getElementById('comment-text-input').value.trim();
        if (!nameVal || !textVal) return;

        const initials = nameVal
          .split(' ')
          .map(n => n[0])
          .join('')
          .toUpperCase()
          .substring(0, 2);

        const newComment = document.createElement('div');
        newComment.className = 'comment-item';
        newComment.innerHTML = `
          <div class="comment-avatar">${initials}</div>
          <div class="comment-body">
            <div class="comment-meta">
              <strong class="comment-author">${nameVal}</strong>
              <span class="comment-date">Just now</span>
            </div>
            <p class="comment-text">${textVal}</p>
          </div>
        `;

        commentList.appendChild(newComment);

        alertBar.style.display = 'block';
        setTimeout(() => {
          alertBar.style.display = 'none';
        }, 3500);

        commentForm.reset();
      });
    }
  });
  </script>

<?php endwhile; endif; ?>

<?php get_footer(); ?>