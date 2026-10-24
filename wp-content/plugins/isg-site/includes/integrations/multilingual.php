<?php
/** Polylang / WPML compatibility and multilingual configuration helpers. */
defined( 'ABSPATH' ) || exit;

/**
 * Keep archive endpoints reachable in every Polylang language.
 * Some Polylang versions/configurations can return 404 for language-prefixed
 * custom-post-type archives even when the content type is marked translatable.
 */
add_action( 'init', 'isg_site_register_localized_archive_rewrite_rules', 100 );
function isg_site_register_localized_archive_rewrite_rules(): void {
    if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_default_language' ) ) {
        return;
    }
    $languages = pll_languages_list( array( 'fields' => 'slug' ) );
    $default_language = (string) pll_default_language( 'slug' );
    if ( ! is_array( $languages ) || empty( $languages ) || '' === $default_language ) {
        return;
    }
    $archives = array( 'products' => 'isg_solution', 'services' => 'isg_service' );

    // Preserve unprefixed default-language routes when that code is hidden in URLs.
    foreach ( $archives as $slug => $post_type ) {
        add_rewrite_rule(
            '^' . preg_quote( $slug, '#' ) . '/?$',
            'index.php?post_type=' . $post_type . '&lang=' . rawurlencode( $default_language ),
            'top'
        );
    }
    foreach ( $languages as $language ) {
        $language = sanitize_key( (string) $language );
        if ( '' === $language ) { continue; }
        foreach ( $archives as $slug => $post_type ) {
            add_rewrite_rule(
                '^' . preg_quote( $language, '#' ) . '/' . preg_quote( $slug, '#' ) . '/?$',
                'index.php?post_type=' . $post_type . '&lang=' . rawurlencode( $language ),
                'top'
            );
        }
    }
}

/** Build a public archive URL in a requested Polylang/WPML language. */
function isg_site_localized_archive_url( string $post_type, string $archive_slug, string $language = '' ): string {
    $archive_slug = trim( $archive_slug, '/' );
    if ( '' === $archive_slug ) { return (string) get_post_type_archive_link( $post_type ); }
    if ( '' === $language && function_exists( 'pll_current_language' ) ) {
        $language = (string) pll_current_language( 'slug' );
    } elseif ( '' === $language && has_filter( 'wpml_current_language' ) ) {
        $language = (string) apply_filters( 'wpml_current_language', null );
    }
    if ( '' !== $language && function_exists( 'pll_home_url' ) ) {
        $language_home = (string) pll_home_url( $language );
        if ( '' !== $language_home ) { return trailingslashit( $language_home ) . $archive_slug . '/'; }
    }
    if ( '' !== $language && has_filter( 'wpml_permalink' ) ) {
        $localized = apply_filters( 'wpml_permalink', home_url( '/' . $archive_slug . '/' ), $language );
        if ( is_string( $localized ) && '' !== $localized ) { return $localized; }
    }
    $archive = get_post_type_archive_link( $post_type );
    return $archive ? (string) $archive : home_url( '/' . $archive_slug . '/' );
}

/** Make all ISG public post types translatable in Polylang. */
add_filter( 'pll_get_post_types', 'isg_site_polylang_post_types', 10, 2 );
function isg_site_polylang_post_types( array $post_types, bool $is_settings = false ): array {
    foreach ( array( 'isg_solution', 'isg_service', 'isg_vacancy', 'isg_partner' ) as $type ) {
        $post_types[ $type ] = $type;
    }
    // WooCommerce products and checkout entities should be registered by the compatible
    // Polylang for WooCommerce extension, which also handles cart/order synchronisation.
    return $post_types;
}

/** Make ISG grouping taxonomies translatable in Polylang. */
add_filter( 'pll_get_taxonomies', 'isg_site_polylang_taxonomies', 10, 2 );
function isg_site_polylang_taxonomies( array $taxonomies, bool $is_settings = false ): array {
    foreach ( array( 'isg_solution_group', 'isg_service_group' ) as $taxonomy ) {
        $taxonomies[ $taxonomy ] = $taxonomy;
    }
    return $taxonomies;
}

/** Copy workflow flags and URLs, but leave text metadata available to translate separately. */
add_filter( 'pll_copy_post_metas', 'isg_site_polylang_copy_post_metas', 10, 2 );
function isg_site_polylang_copy_post_metas( array $metas, bool $sync = false ): array {
    // Copy workflow flags, routing URLs and the invariant case year only.
    // Language-specific copy (subtitle, CTA label, price note, location, salary and demo note)
    // must remain editable/translateable in the target-language record.
    foreach ( array( 'isg_demo_content', 'isg_case_year', 'isg_cta_url', 'isg_demo_url' ) as $meta_key ) {
        if ( ! in_array( $meta_key, $metas, true ) ) { $metas[] = $meta_key; }
    }
    return $metas;
}

/** Admin setup checklist so test deploys do not pretend translations exist. */
// Register this child page after the parent 'ISG Site' menu has been created.
// The parent menu is registered by includes/admin/demo-content.php on admin_menu priority 10.
add_action( 'admin_menu', 'isg_site_register_language_setup_page', 20 );
function isg_site_register_language_setup_page(): void {
    add_submenu_page( 'isg-demo-content', 'Multilingual setup', 'Languages', 'manage_options', 'isg-language-setup', 'isg_site_render_language_setup_page' );
}
function isg_site_render_language_setup_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    $polylang = function_exists( 'pll_the_languages' );
    $wpml = defined( 'ICL_SITEPRESS_VERSION' ) || has_filter( 'wpml_active_languages' );
    echo '<div class="wrap"><h1>Multilingual setup</h1><p><a class="button button-secondary" href="' . esc_url( admin_url( 'admin.php?page=isg-demo-content' ) ) . '">← Демо-наполнение</a></p>'; 
    if ( $polylang || $wpml ) {
        echo '<div class="notice notice-success"><p><strong>Language plugin detected:</strong> ' . esc_html( $polylang ? 'Polylang' : 'WPML' ) . '. The theme language switcher can use the plugin-generated translation URLs.</p></div>';
    } else {
        echo '<div class="notice notice-warning"><p><strong>No multilingual plugin detected.</strong> The language control is intentionally disabled until real language routes and translated content exist.</p></div>';
    }
    echo '<h2>Recommended test configuration</h2><ol><li>Set <strong>English (en_US)</strong> as the default language and add <strong>Russian (ru_RU)</strong>.</li><li>Use language directories and keep the language code visible for the default language: <code>/en/</code> and <code>/ru/</code>.</li><li>Enable browser-language detection for first-time visitors, but keep the manual switcher visible.</li><li>Enable translation for ISG solutions, services, vacancies, case studies, their taxonomies, pages, news and WooCommerce products.</li><li>Review the registered ‘ISG Theme’ strings under Languages → Translations, then translate and review them before publishing.</li></ol>';
    echo '<h2>Machine translation</h2><p>For neural translation of individual items, use Polylang Pro with a DeepL API key. Translation is performed per post/page/product and for registered theme strings; the entire website is not translated in one click. The API key is stored in the multilingual plugin, not in this project.</p><p>For WooCommerce translations, install the compatible Polylang for WooCommerce add-on. Without the appropriate plugin/add-on and published translated records, the language switcher cannot create the translations for you.</p>';
    echo '<h2>Compatibility</h2><p>This project registers ISG custom post types and grouping taxonomies with Polylang and supports real language links from Polylang or WPML. Slugs, translated WooCommerce attributes, cart/session synchronization and SEO hreflang still need to be verified on the target site.</p></div>';
}


/** Expose public ISG custom fields to Polylang Pro's translation export / DeepL flow. */
add_filter( 'pll_post_metas_to_export', 'isg_site_polylang_translatable_meta', 10, 1 );
function isg_site_polylang_translatable_meta( array $metas ): array {
    foreach ( array( 'isg_subtitle', 'isg_cta_label', 'isg_price_note', 'isg_vacancy_location', 'isg_vacancy_salary', 'isg_case_client', 'isg_case_year', 'isg_demo_note' ) as $key ) {
        $metas[ $key ] = 1;
    }
    return $metas;
}
