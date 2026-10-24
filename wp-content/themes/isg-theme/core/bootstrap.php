<?php
/**
 * Loads theme modules.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'ISG_THEME_VERSION' ) ) {
	$theme = wp_get_theme();
	define( 'ISG_THEME_VERSION', $theme->get( 'Version' ) ?: '0.2.0' );
}

if ( ! defined( 'ISG_THEME_PATH' ) ) {
	define( 'ISG_THEME_PATH', trailingslashit( get_template_directory() ) );
}

if ( ! defined( 'ISG_THEME_URL' ) ) {
	define( 'ISG_THEME_URL', trailingslashit( get_template_directory_uri() ) );
}

require_once ISG_THEME_PATH . 'core/setup.php';
require_once ISG_THEME_PATH . 'core/i18n.php';
require_once ISG_THEME_PATH . 'core/assets.php';
require_once ISG_THEME_PATH . 'core/template-tags.php';
require_once ISG_THEME_PATH . 'core/woocommerce.php';
