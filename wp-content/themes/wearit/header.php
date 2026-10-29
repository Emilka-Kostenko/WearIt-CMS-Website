<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500&family=Barlow+Condensed:wght@900&display=swap" rel="stylesheet" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- Skip link for keyboard users (WCAG 2.4.1). Target: #main, set below on the page's <main>. -->
<a class="skip-link" href="#main"><?php echo esc_html( wearit_account_text( 'skip_link_text', 'Skip to content' ) ); ?></a>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    // Not every template's <main> has an id (single.php has no <main> at all), so find the content here.
    const target = document.getElementById('main') || document.querySelector('main') || document.getElementById('navbar').nextElementSibling;
    if (!target) return;
    target.id = target.id || 'main';
    target.setAttribute('tabindex', '-1'); // so focus actually moves there, not just the scroll
  });
</script>

<header class="navbar" id="navbar">
  <div class="navbar__container">

    <!-- Logo — SCF field: site_logo_text (or use get_bloginfo) -->
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar__logo">
      <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
    </a>

    <!-- Navigation links -->
    <nav class="navbar__nav" id="navbar-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'wearit' ); ?>">
      <ul class="navbar__menu">
        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'product' ) ); ?>" class="navbar__link"><?php esc_html_e( 'Shop', 'wearit' ); ?></a></li>
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'blog' ) ) ); ?>" class="navbar__link"><?php esc_html_e( 'Blog', 'wearit' ); ?></a></li>

        <li><a href="<?php echo esc_url( get_post_type_archive_link( 'initiative' ) ); ?>" class="navbar__link"><?php esc_html_e( 'Sustainability', 'wearit' ); ?></a></li>
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'about' ) ) ); ?>" class="navbar__link"><?php esc_html_e( 'About', 'wearit' ); ?></a></li>
      </ul>
    </nav>

    <!-- Right actions -->
    <div class="navbar__actions">
      <nav class="lang-switch" aria-label="<?php esc_attr_e( 'Language', 'wearit' ); ?>">
        <a href="<?php echo esc_url( wearit_lang_url( 'en' ) ); ?>" class="lang-switch__link" lang="en" hreflang="en" aria-label="English (EN)"<?php echo wearit_is_danish() ? '' : ' aria-current="true"'; ?>>EN</a>
        <a href="<?php echo esc_url( wearit_lang_url( 'da' ) ); ?>" class="lang-switch__link" lang="da" hreflang="da" aria-label="Dansk (DA)"<?php echo wearit_is_danish() ? ' aria-current="true"' : ''; ?>>DA</a>
      </nav>
      <button class="navbar__icon-btn" aria-label="<?php esc_attr_e( 'Search', 'wearit' ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
          <path d="M0 0h24v24H0z" fill="none" />
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m15 15l6 6m-11-4a7 7 0 1 1 0-14a7 7 0 0 1 0 14" />
        </svg>
      </button>
      <!-- Account — log in / register / overview (inc/account.php, accountPage.php) -->
      <a href="<?php echo esc_url( wearit_account_url() ); ?>" class="navbar__icon-btn<?php echo is_user_logged_in() ? ' is-logged-in' : ''; ?>" aria-label="<?php echo esc_attr( is_user_logged_in() ? wearit_account_text( 'account_icon_label_logged_in', 'Your account' ) : wearit_account_text( 'account_icon_label_logged_out', 'Log in or create an account' ) ); ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 12a4 4 0 1 0 0-8a4 4 0 0 0 0 8m-7 8a7 7 0 0 1 14 0" />
        </svg>
      </a>
      <!-- Mini-cart — logic in inc/cart.php + assets/js/cart.js -->
      <?php $cart_count = wearit_get_cart()['count']; ?>
      <div class="mini-cart" id="mini-cart">
        <button class="navbar__icon-btn mini-cart__toggle" id="mini-cart-toggle" aria-label="<?php esc_attr_e( 'Shopping bag', 'wearit' ); ?>" aria-expanded="false" aria-controls="mini-cart-panel">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 1024 1024">
            <path d="M0 0h1024v1024H0z" fill="none" />
            <path fill="currentColor"
              d="M704 320v96a32 32 0 0 1-32 32h-32V320H384v128h-32a32 32 0 0 1-32-32v-96H192v576h640V320zm-384-64a192 192 0 1 1 384 0h160a32 32 0 0 1 32 32v640a32 32 0 0 1-32 32H160a32 32 0 0 1-32-32V288a32 32 0 0 1 32-32zm64 0h256a128 128 0 1 0-256 0" />
            <path fill="currentColor" d="M192 704h640v64H192z" />
          </svg>
          <span class="mini-cart__badge" id="mini-cart-badge"<?php echo $cart_count ? '' : ' hidden'; ?>><?php echo esc_html( $cart_count ); ?></span>
        </button>

        <div class="mini-cart__panel" id="mini-cart-panel" hidden>
          <?php get_template_part( 'template-parts/mini-cart' ); ?>
        </div>
      </div>
    </div>

    <!-- Hamburger (mobile) -->
    <button class="navbar__hamburger" id="hamburger" aria-label="<?php esc_attr_e( 'Open menu', 'wearit' ); ?>" aria-expanded="false" aria-controls="navbar-nav">
      <span></span>
      <span></span>
      <span></span>
    </button>

  </div>
</header>

<script>
  const hamburger = document.getElementById('hamburger');
  const nav = document.getElementById('navbar-nav');
  hamburger.addEventListener('click', () => {
    const isOpen = nav.classList.toggle('is-open');
    hamburger.classList.toggle('is-active', isOpen);
    hamburger.setAttribute('aria-expanded', isOpen);
  });
  document.querySelectorAll('.navbar__link').forEach(link => {
    link.addEventListener('click', () => {
      nav.classList.remove('is-open');
      hamburger.classList.remove('is-active');
      hamburger.setAttribute('aria-expanded', 'false');
    });
  });
</script>
