<?php
/**
 * Plugin Name: ISG Site Functionality
 * Description: Site-specific content models and demo functionality for the ISG test assignment.
 * Version: 0.4.7
 * Requires at least: 6.6
 * Requires PHP: 8.1
 * Author: Роман Литвиненко
 * Text Domain: isg-site
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 * @package ISG_Site
 */

defined( 'ABSPATH' ) || exit;

define( 'ISG_SITE_VERSION', '0.4.6' );
define( 'ISG_SITE_FILE', __FILE__ );
define( 'ISG_SITE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ISG_SITE_URL', plugin_dir_url( __FILE__ ) );

require_once ISG_SITE_PATH . 'includes/bootstrap.php';

register_activation_hook( __FILE__, 'isg_site_activate' );
function isg_site_activate(): void {
    require_once ISG_SITE_PATH . 'includes/content/post-types.php';
    require_once ISG_SITE_PATH . 'includes/content/taxonomies.php';
    isg_site_register_post_types();
    isg_site_register_taxonomies();
    flush_rewrite_rules();
    update_option( 'isg_site_version', ISG_SITE_VERSION );
}
add_action( 'admin_init', 'isg_site_maybe_update_rewrite_rules' );
function isg_site_maybe_update_rewrite_rules(): void {
    if ( get_option( 'isg_site_version' ) !== ISG_SITE_VERSION ) {
        flush_rewrite_rules( false );
        update_option( 'isg_site_version', ISG_SITE_VERSION );

        // Repair only the common generated-menu assignment on deployment/upgrade,
        // while leaving a populated, valid manual primary menu in place.
        if ( function_exists( 'isg_site_ensure_primary_menu' ) ) {
            isg_site_ensure_primary_menu();
        }
        if ( function_exists( 'isg_site_ensure_russian_demo_menu' ) ) {
            isg_site_ensure_russian_demo_menu();
        }
        if ( function_exists( 'isg_site_repair_shop_page_assignment' ) ) {
            isg_site_repair_shop_page_assignment();
        }
    }
}

register_deactivation_hook( __FILE__, 'isg_site_deactivate' );
function isg_site_deactivate(): void {
    flush_rewrite_rules();
}
