<?php
/**
 * Template Name: About Page
 *
 * Story section plus a video. The video is NOT a normal YouTube embed — that
 * pulls in over a megabyte of scripts on every page load even if nobody presses play.
 * Instead we show the thumbnail and only load the real player on click.
 * See wearit_youtube_id() for how the link is turned into an id.
 */
get_header();

// Paste any youtube link into the SCF field, this digs the id out of it bleh
$video_url = get_field( 'about_video_url' );
$video_id  = wearit_youtube_id( $video_url );
?>

<main class="shop about-page">

  <!-- ── HERO ── -->
  <section class="shop-hero">
    <div class="shop-hero__container">
      <p class="shop-hero__eyebrow">
        <?php
        $eyebrow = get_field( 'about_eyebrow' );
        echo $eyebrow ? esc_html( $eyebrow ) : 'Who we are';
        ?>
      </p>
      <h1 class="shop-hero__heading">
        <?php
        $heading = get_field( 'about_heading' );
        echo $heading ? esc_html( $heading ) : 'About';
        ?>
        <span class="shop-hero__heading--accent">
          <?php
          $accent = get_field( 'about_heading_accent' );
          echo $accent ? esc_html( $accent ) : 'Us';
          ?>
        </span>
      </h1>
    </div>
  </section>

  <section class="shop-body">
    <div class="about-body__container">

      <!-- ── THE STORY ── -->
      <div class="about-story">
        <h2 class="about-story__title">
          <?php
          $story_title = get_field( 'about_story_title' );
          echo $story_title ? esc_html( $story_title ) : 'Built for the streets';
          ?>
        </h2>
        <p class="about-story__text">
          <?php
          $story_text = get_field( 'about_story_text' );
          echo $story_text ? esc_html( $story_text ) : 'We started WearIt because everything on the rails felt the same. Thin fabric, safe shapes, a logo doing the heavy lifting. We wanted the opposite — heavy cotton, sharp cuts, pieces that look better after a year than they did in the shop.';
          ?>
        </p>
        <p class="about-story__text">
          <?php
          $story_text2 = get_field( 'about_story_text2' );
          echo $story_text2 ? esc_html( $story_text2 ) : 'No investors, no shortcuts. Every drop is small, made properly, and gone when it is gone.';
          ?>
        </p>
      </div>

      <!-- ── THE VIDEO — thumbnail first ── -->
      <?php if ( $video_id ) : ?>
        <div class="about-video">
          <h2 class="about-video__title">
            <?php
            $video_title = get_field( 'about_video_title' );
            echo $video_title ? esc_html( $video_title ) : 'See the last drop';
            ?>
          </h2>

          <div
            class="about-video__frame"
            data-youtube="<?php echo esc_attr( $video_id ); ?>"
          >
            <!-- the thumbnail comes straight from youtube, about 30kb instead of a megabyte -->
            <img
              class="about-video__thumb"
              src="https://img.youtube.com/vi/<?php echo esc_attr( $video_id ); ?>/hqdefault.jpg"
              alt=""
              loading="lazy"
              width="480"
              height="360"
            />
            <button type="button" class="about-video__play" aria-label="Play the video">
              <span class="about-video__play-icon" aria-hidden="true">&#9654;</span>
            </button>
          </div>

        </div>
      <?php endif; ?>

    </div>
  </section>

</main>

<script>
  // Swap the thumbnail 
  (function () {
    document.querySelectorAll('.about-video__frame').forEach(function (frame) {
      frame.addEventListener('click', function () {
        if (frame.classList.contains('is-playing')) return;
        var id = frame.getAttribute('data-youtube');
        var iframe = document.createElement('iframe');
        // nocookie + autoplay so it starts straight away after the click
        iframe.src = 'https://www.youtube-nocookie.com/embed/' + id + '?autoplay=1';
        iframe.title = 'Video';
        iframe.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.className = 'about-video__iframe';
        frame.innerHTML = '';
        frame.appendChild(iframe);
        frame.classList.add('is-playing');
      });
    });
  })();
</script>

<?php get_footer(); ?>