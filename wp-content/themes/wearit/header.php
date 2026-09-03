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

<header class="navbar" id="navbar">
  <div class="navbar__container">

    <!-- Logo — SCF field: site_logo_text (or use get_bloginfo) -->
    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="navbar__logo">
      <?php echo esc_html( get_bloginfo( 'name' ) ); ?>
    </a>

    <!-- Navigation links -->
    <nav class="navbar__nav" id="navbar-nav" aria-label="Main navigation">
      <ul class="navbar__menu">
        <li><a href="#" class="navbar__link">Shop</a></li>
        <li><a href="#" class="navbar__link">Categories</a></li>
        <li><a href="<?php echo esc_url( get_permalink( get_page_by_path( 'blog' ) ) ); ?>" class="navbar__link">Blog</a></li>
    
        <li><a href="#" class="navbar__link">About</a></li>
      </ul>
    </nav>

    <!-- Right actions -->
    <div class="navbar__actions">
      <button class="navbar__icon-btn" aria-label="Search">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24">
          <path d="M0 0h24v24H0z" fill="none" />
          <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m15 15l6 6m-11-4a7 7 0 1 1 0-14a7 7 0 0 1 0 14" />
        </svg>
      </button>
      <button class="navbar__icon-btn" aria-label="Shopping bag">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 1024 1024">
          <path d="M0 0h1024v1024H0z" fill="none" />
          <path fill="currentColor"
            d="M704 320v96a32 32 0 0 1-32 32h-32V320H384v128h-32a32 32 0 0 1-32-32v-96H192v576h640V320zm-384-64a192 192 0 1 1 384 0h160a32 32 0 0 1 32 32v640a32 32 0 0 1-32 32H160a32 32 0 0 1-32-32V288a32 32 0 0 1 32-32zm64 0h256a128 128 0 1 0-256 0" />
          <path fill="currentColor" d="M192 704h640v64H192z" />
        </svg>
      </button>
    </div>

    <!-- Hamburger (mobile) -->
    <button class="navbar__hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false" aria-controls="navbar-nav">
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
