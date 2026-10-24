<?php
/**
 * Front-end and editor assets.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'isg_theme_enqueue_assets' );

/**
 * Enqueue styles and scripts.
 *
 * @return void
 */
function isg_theme_enqueue_assets(): void {
	wp_enqueue_style(
		'isg-theme-style',
		get_stylesheet_uri(),
		array(),
		isg_theme_asset_version( '/style.css' )
	);

	wp_enqueue_style(
		'isg-theme-main',
		ISG_THEME_URL . 'assets/css/theme.css',
		array( 'isg-theme-style' ),
		isg_theme_asset_version( '/assets/css/theme.css' )
	);

	wp_enqueue_script(
		'isg-theme-main',
		ISG_THEME_URL . 'assets/js/theme.js',
		array(),
		isg_theme_asset_version( '/assets/js/theme.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
}

/**
 * Return a file modification time for cache busting.
 *
 * @param string $relative_path Theme-relative path beginning with a slash.
 * @return string
 */
function isg_theme_asset_version( string $relative_path ): string {
	$file_path = get_theme_file_path( $relative_path );

	if ( is_readable( $file_path ) ) {
		return (string) filemtime( $file_path );
	}

	return (string) ISG_THEME_VERSION;
}
