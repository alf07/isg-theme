<?php
/**
 * WooCommerce presentation support for the theme.
 * Business rules and integration behavior belong in the isg-site plugin.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', 'isg_theme_woocommerce_setup' );

/**
 * Declare the WooCommerce features supported by this theme.
 *
 * @return void
 */
function isg_theme_woocommerce_setup(): void {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}


/**
 * Resolve the store URL for the current language.
 * It validates the page object first so a stale WooCommerce setting can never
 * send the header button to an individual product.
 */
function isg_theme_shop_url(): string {
    $shop_page = null;
    $shop_page_id = 0;

    // Prefer the canonical page by slug, irrespective of the active language query filter.
    $pages = get_posts( array(
        'post_type' => 'page',
        'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
        'name' => 'shop',
        'posts_per_page' => 1,
        'suppress_filters' => true,
        'orderby' => 'ID',
        'order' => 'ASC',
    ) );
    if ( ! empty( $pages ) && $pages[0] instanceof WP_Post ) {
        $shop_page = $pages[0];
        $shop_page_id = (int) $shop_page->ID;
    }

    // Support an explicitly marked shop page if an administrator has retained a translated/custom slug.
    if ( ! $shop_page_id ) {
        $marked = get_posts( array(
            'post_type' => 'page',
            'post_status' => array( 'publish', 'private', 'draft', 'pending' ),
            'meta_key' => '_isg_demo_shop_page',
            'meta_value' => '1',
            'posts_per_page' => 1,
            'suppress_filters' => true,
            'orderby' => 'ID',
            'order' => 'ASC',
        ) );
        if ( ! empty( $marked ) && $marked[0] instanceof WP_Post ) {
            $shop_page = $marked[0];
            $shop_page_id = (int) $shop_page->ID;
        }
    }

    // If WooCommerce has no valid seeded page, accept its setting only when it points to a page,
    // never to a product/custom post type. This is a final fallback, not the primary lookup.
    if ( ! $shop_page_id && function_exists( 'wc_get_page_id' ) ) {
        $configured_id = absint( wc_get_page_id( 'shop' ) );
        $configured = $configured_id ? get_post( $configured_id ) : null;
        if ( $configured instanceof WP_Post && 'page' === $configured->post_type && 'trash' !== $configured->post_status ) {
            $shop_page = $configured;
            $shop_page_id = (int) $configured->ID;
        }
    }

    $language = '';
    $default_language = '';
    if ( function_exists( 'pll_current_language' ) ) {
        $language = (string) pll_current_language( 'slug' );
        $default_language = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : 'en';
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $language = (string) apply_filters( 'wpml_current_language', null );
        $default_language = (string) apply_filters( 'wpml_default_language', null );
    }

    if ( $shop_page_id ) {
        $localized_id = $shop_page_id;
        if ( '' !== $language && function_exists( 'isg_theme_localized_post_id' ) ) {
            $localized_id = isg_theme_localized_post_id( $shop_page_id );
        }
        $localized_page = $localized_id ? get_post( $localized_id ) : null;
        if ( $localized_page instanceof WP_Post && 'page' === $localized_page->post_type && 'trash' !== $localized_page->post_status ) {
            $url = get_permalink( $localized_page->ID );
            if ( is_string( $url ) && '' !== $url && isg_theme_is_internal_url( $url ) ) {
                return $url;
            }
        }

        // No translated shop page exists yet. Use the product archive query on the active language home URL,
        // instead of sending the visitor to the default-language shop page or any product permalink.
        if ( '' !== $language && '' !== $default_language && $language !== $default_language ) {
            $language_home = home_url( '/' );
            if ( function_exists( 'pll_home_url' ) ) {
                $language_home = (string) pll_home_url( $language );
            } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
                $language_home = (string) apply_filters( 'wpml_permalink', home_url( '/' ), $language, true );
            }
            if ( isg_theme_is_internal_url( $language_home ) ) {
                return add_query_arg( 'post_type', 'product', $language_home );
            }
        }
    }

    // WooCommerce's own shop permalink is safe only when its configured ID is actually a page.
    if ( function_exists( 'wc_get_page_id' ) && function_exists( 'wc_get_page_permalink' ) ) {
        $configured_id = absint( wc_get_page_id( 'shop' ) );
        $configured = $configured_id ? get_post( $configured_id ) : null;
        if ( $configured instanceof WP_Post && 'page' === $configured->post_type && 'trash' !== $configured->post_status ) {
            $url = (string) wc_get_page_permalink( 'shop' );
            if ( '' !== $url && isg_theme_is_internal_url( $url ) ) { return $url; }
        }
    }

    $archive_url = get_post_type_archive_link( 'product' );
    if ( is_string( $archive_url ) && '' !== $archive_url && isg_theme_is_internal_url( $archive_url ) ) {
        return $archive_url;
    }

    // Last resort remains a local store path and can never be a product URL.
    return home_url( '/shop/' );
}
