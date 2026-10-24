<?php
/**
 * Shared localization helpers. English is the source language; Russian fallback
 * copy is retained for test preview when a translation plugin is not configured.
 * @package ISG_Theme
 */
defined( 'ABSPATH' ) || exit;

/** Get the deduplicated English string catalog. */
function isg_theme_translation_catalog(): array {
    static $catalog = null;
    if ( null === $catalog ) {
        $strings = require ISG_THEME_PATH . 'core/i18n-strings.php';
        $catalog = array_fill_keys( $strings, true );
    }
    return $catalog;
}

/** Register translatable theme strings once during initialization, not in every template render. */
add_action( 'init', 'isg_theme_register_translation_catalog', 25 );
function isg_theme_register_translation_catalog(): void {
    $registered = array();
    foreach ( array_keys( isg_theme_translation_catalog() ) as $source ) {
        if ( function_exists( 'pll_register_string' ) ) {
            pll_register_string( 'isg-theme-' . substr( md5( $source ), 0, 12 ), $source, 'ISG Theme', false );
        }
        if ( has_filter( 'wpml_translate_single_string' ) ) {
            do_action( 'wpml_register_single_string', 'ISG Theme', $source, $source );
        }
        $registered[ $source ] = true;
    }
    $GLOBALS['isg_theme_registered_strings'] = $registered;
}

/** Translate a source-English string with a Russian fallback for an unconfigured demo. */
function isg_theme_t( string $source, string $russian_fallback = '' ): string {
    $source = trim( $source );
    if ( '' === $source ) { return ''; }

    // Runtime values (editable homepage options) may not be in the static catalog.
    if ( empty( $GLOBALS['isg_theme_registered_strings'][ $source ] ) ) {
        if ( function_exists( 'pll_register_string' ) ) {
            pll_register_string( 'isg-theme-' . substr( md5( $source ), 0, 12 ), $source, 'ISG Theme', false );
        }
        if ( has_filter( 'wpml_translate_single_string' ) ) {
            do_action( 'wpml_register_single_string', 'ISG Theme', $source, $source );
        }
        $GLOBALS['isg_theme_registered_strings'][ $source ] = true;
    }

    if ( function_exists( 'pll__' ) ) {
        $translated = pll__( $source );
        if ( is_string( $translated ) && '' !== $translated && $translated !== $source ) { return $translated; }
    }

    if ( has_filter( 'wpml_translate_single_string' ) ) {
        $translated = apply_filters( 'wpml_translate_single_string', $source, 'ISG Theme', $source );
        if ( is_string( $translated ) && '' !== $translated && $translated !== $source ) { return $translated; }
    }

    $language = '';
    if ( function_exists( 'pll_current_language' ) ) {
        $language = (string) pll_current_language( 'slug' );
    } elseif ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $language = (string) apply_filters( 'wpml_current_language', null );
    } else {
        $language = substr( (string) ( function_exists( 'determine_locale' ) ? determine_locale() : get_locale() ), 0, 2 );
    }

    if ( 0 === strpos( strtolower( $language ), 'ru' ) && '' !== $russian_fallback ) { return $russian_fallback; }
    return __( $source, 'isg-theme' );
}

/** Return the translation ID for the active language, or 0 when none exists. */
function isg_theme_localized_post_id( int $post_id ): int {
    if ( $post_id < 1 ) { return 0; }
    if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_get_post' ) ) {
        $language = (string) pll_current_language( 'slug' );
        $localized = pll_get_post( $post_id, $language );
        if ( $localized ) { return (int) $localized; }
        $default = function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : 'en';
        return $language === $default ? $post_id : 0;
    }
    if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        $language = (string) apply_filters( 'wpml_current_language', null );
        $type = get_post_type( $post_id );
        $localized = apply_filters( 'wpml_object_id', $post_id, $type, false, $language );
        if ( $localized ) { return (int) $localized; }
        $default = (string) apply_filters( 'wpml_default_language', null );
        return $language === $default ? $post_id : 0;
    }
    return $post_id;
}


/** Build a permalink to the page translation, preserving the active language. */
function isg_theme_localized_page_url( string $slug, string $fallback_path = '/' ): string {
    $page = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $page instanceof WP_Post ) {
        $localized_id = isg_theme_localized_post_id( (int) $page->ID );
        if ( $localized_id ) {
            $url = get_permalink( $localized_id );
            if ( $url ) { return $url; }
        }
        if ( function_exists( 'pll_current_language' ) && function_exists( 'pll_default_language' ) && pll_current_language( 'slug' ) !== pll_default_language( 'slug' ) ) {
            return home_url( '/' );
        }
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
            $language = (string) apply_filters( 'wpml_current_language', null );
            $default = (string) apply_filters( 'wpml_default_language', null );
            if ( $language !== $default ) { return home_url( '/' ); }
        }
        return (string) get_permalink( $page );
    }
    return home_url( $fallback_path );
}
