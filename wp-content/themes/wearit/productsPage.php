<?php
/**
 * Template Name: Products Page
 *
 * Holds the SCF text for the shop (field group "Products Page", see inc/shop-page.php).
 * The shop itself is archive-product.php at /products/, so this page only sends
 * visitors there.
 */
wp_safe_redirect( get_post_type_archive_link( 'product' ), 301 );
exit;
