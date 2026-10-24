<?php
/**
 * Theme setup: supports, menus and translations.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'isg_theme_setup' );

/**
 * Register theme features.
 *
 * @return void
 */
function isg_theme_setup(): void {
	load_theme_textdomain( 'isg-theme', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 280,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'script',
		'style',
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	set_post_thumbnail_size( 1200, 800, false );
	add_image_size( 'isg-theme-card', 640, 480, true );

	register_nav_menus( array(
		'primary' => __( 'Primary navigation', 'isg-theme' ),
		'utility' => __( 'Utility navigation', 'isg-theme' ),
		'footer'  => __( 'Footer navigation', 'isg-theme' ),
	) );
}
