<?php
/** WooCommerce page wrapper. Product/catalog data remains owned by WooCommerce. */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main-content" class="site-main shop-main"><div class="container shop-container">
<?php woocommerce_content(); ?>
</div></main>
<?php get_footer(); ?>
