<footer class="footer">
  <div class="footer__container">

    <div class="footer__top">

      <!-- Brand col — SCF field: footer_tagline -->
      <div class="footer__brand">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="footer__logo">
          <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
        </a>
        <p class="footer__tagline">
          <?php
          $tagline = get_field( 'footer_tagline' );
          echo $tagline ? esc_html( $tagline ) : 'Built for the streets. Made to last.';
          ?>
        </p>
      </div>

      <!-- Nav columns — SCF fields: footer_cs_links, footer_info_links, footer_social_links -->
      <div class="footer__nav">

        <div class="footer__col">
          <p class="footer__col-title">Customer Service</p>
          <ul class="footer__col-links">
            <li><a href="#" class="footer__link">Delivery</a></li>
            <li><a href="#" class="footer__link">Returns</a></li>
            <li><a href="#" class="footer__link">FAQ</a></li>
          </ul>
        </div>

        <div class="footer__col">
          <p class="footer__col-title">Information</p>
          <ul class="footer__col-links">
            <li><a href="#" class="footer__link">About</a></li>
            <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="footer__link">Contact</a></li>
          </ul>
        </div>

        <div class="footer__col">
          <p class="footer__col-title">Social Media</p>
          <ul class="footer__col-links">
            <li><a href="#" class="footer__link">Instagram</a></li>
            <li><a href="#" class="footer__link">Facebook</a></li>
          </ul>
        </div>

      </div>
    </div>

    <!-- Bottom bar — SCF field: footer_copyright -->
    <div class="footer__bottom">
      <p class="footer__copy">
        <?php
        $copy = get_field( 'footer_copyright' );
        echo $copy ? esc_html( $copy ) : '&copy; ' . date( 'Y' ) . ' WearIt. All rights reserved.';
        ?>
      </p>
      <p class="footer__legal">Move for the streets.</p>
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
