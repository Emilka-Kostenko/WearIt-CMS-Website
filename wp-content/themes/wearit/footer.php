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
          echo $tagline ? esc_html( $tagline ) : esc_html__( 'Built for the streets. Made to last.', 'wearit' );
          ?>
        </p>
      </div>

      <!-- Nav columns — SCF fields: footer_cs_links, footer_info_links, footer_social_links -->
      <div class="footer__nav">

        <div class="footer__col">
          <p class="footer__col-title"><?php esc_html_e( 'Customer Service', 'wearit' ); ?></p>
          <ul class="footer__col-links">
            <li><a href="#" class="footer__link"><?php esc_html_e( 'Delivery', 'wearit' ); ?></a></li>
            <li><a href="#" class="footer__link"><?php esc_html_e( 'Returns', 'wearit' ); ?></a></li>
            <li><a href="#" class="footer__link"><?php esc_html_e( 'FAQ', 'wearit' ); ?></a></li>
          </ul>
        </div>

        <div class="footer__col">
          <p class="footer__col-title"><?php esc_html_e( 'Information', 'wearit' ); ?></p>
          <ul class="footer__col-links">
                        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>" class="footer__link"><?php esc_html_e( 'About', 'wearit' ); ?></a></li>
            <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'contact' ) ) ); ?>" class="footer__link"><?php esc_html_e( 'Contact', 'wearit' ); ?></a></li>
          </ul>
        </div>

        <div class="footer__col">
          <p class="footer__col-title"><?php esc_html_e( 'Social Media', 'wearit' ); ?></p>
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
        echo $copy ? esc_html( $copy ) : '&copy; ' . date( 'Y' ) . ' ' . esc_html__( 'WearIt. All rights reserved.', 'wearit' );
        ?>
      </p>
    
    </div>

  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
