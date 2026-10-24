<?php
/**
 * Reusable template helpers.
 * Keep helpers small and presentation-focused.
 *
 * @package ISG_Theme
 */

defined( 'ABSPATH' ) || exit;


/**
 * Allow only same-site URLs in frontend links configured through the admin.
 * Relative paths, fragments and query-relative URLs remain valid.
 */
function isg_theme_is_internal_url( string $url ): bool {
    $url = trim( html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
    if ( '' === $url ) { return false; }

    $parts = wp_parse_url( $url );
    if ( false === $parts ) { return false; }
    $scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
    $host = strtolower( rtrim( (string) ( $parts['host'] ?? '' ), '.' ) );

    if ( '' === $host ) {
        // Reject executable/communication schemes such as javascript:, mailto: and tel:.
        return '' === $scheme;
    }
    if ( ! in_array( $scheme, array( '', 'http', 'https' ), true ) ) { return false; }

    $site_hosts = array(
        strtolower( rtrim( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ), '.' ) ),
        strtolower( rtrim( (string) wp_parse_url( site_url( '/' ), PHP_URL_HOST ), '.' ) ),
    );
    $normalize = static function ( string $value ): string {
        return preg_replace( '/^www\./i', '', strtolower( rtrim( $value, '.' ) ) );
    };
    $host = $normalize( $host );
    foreach ( array_filter( $site_hosts ) as $site_host ) {
        if ( $host === $normalize( $site_host ) ) { return true; }
    }
    return false;
}

/** Return an internal URL or an empty string when a third-party URL was supplied. */
function isg_theme_internal_url_or_empty( string $url ): string {
    return isg_theme_is_internal_url( $url ) ? trim( $url ) : '';
}

/** Strip external custom links from frontend navigation menus. */
add_filter( 'wp_nav_menu_objects', 'isg_theme_remove_external_menu_items', 100, 2 );
function isg_theme_remove_external_menu_items( array $items, $args = null ): array {
    foreach ( $items as $key => $item ) {
        if ( isset( $item->url ) && '' !== (string) $item->url && ! isg_theme_is_internal_url( (string) $item->url ) ) {
            unset( $items[ $key ] );
        }
    }
    return array_values( $items );
}

/**
 * Render the post date.
 *
 * @return void
 */
function isg_theme_posted_on(): void {
	$permalink = get_permalink();
	$date      = get_the_date();

	if ( ! $permalink || ! $date ) {
		return;
	}

	printf(
		'<time class="entry-meta__date" datetime="%1$s"><a href="%2$s">%3$s</a></time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_url( $permalink ),
		esc_html( $date )
	);
}

/**
 * Render the post author.
 *
 * @return void
 */
function isg_theme_posted_by(): void {
	echo '<span class="entry-meta__author">' . esc_html( isg_theme_t( 'Roman Litvinenko', 'Роман Литвиненко' ) ) . '</span>';
}


/**
 * Fallback primary menu for a freshly activated theme.
 *
 * The items give the design a useful starting point before a CMS-managed menu
 * is configured. Real menu items override this fallback automatically.
 *
 * @param array $args Arguments passed by wp_nav_menu().
 * @return string
 */
function isg_theme_primary_menu_fallback( $args ): string {
	$language = '';
	if ( function_exists( 'pll_current_language' ) ) {
		$language = (string) pll_current_language( 'slug' );
	} elseif ( has_filter( 'wpml_current_language' ) ) {
		$language = (string) apply_filters( 'wpml_current_language', null );
	}
	$products_url = function_exists( 'isg_site_localized_archive_url' )
		? isg_site_localized_archive_url( 'isg_solution', 'products', $language )
		: ( get_post_type_archive_link( 'isg_solution' ) ?: home_url( '/products/' ) );
	$services_url = function_exists( 'isg_site_localized_archive_url' )
		? isg_site_localized_archive_url( 'isg_service', 'services', $language )
		: ( get_post_type_archive_link( 'isg_service' ) ?: home_url( '/services/' ) );
	$items = array(
		'Products' => $products_url,
		'Services'   => $services_url,
		'Industries'    => '/#industries',
		'Company' => '/#company',
		'Support' => '/support/',
	);

	$menu_class = 'site-navigation__list';
	if ( is_array( $args ) && ! empty( $args['menu_class'] ) ) {
		$menu_class = (string) $args['menu_class'];
	}

	$output = '<ul class="' . esc_attr( $menu_class ) . '">';
	foreach ( $items as $label => $path ) {
		$ru_labels = array( 'Products' => 'Продукты', 'Services' => 'Услуги', 'Industries' => 'Сферы', 'Company' => 'Компания', 'Support' => 'Поддержка' );
		$url = in_array( $label, array( 'Products', 'Services' ), true ) ? (string) $path : ( 0 === strpos( (string) $path, 'http' ) ? (string) $path : home_url( $path ) );
		$output .= '<li class="menu-item"><a href="' . esc_url( $url ) . '">' . esc_html( isg_theme_t( $label, $ru_labels[ $label ] ?? '' ) ) . '</a></li>';
	}
	$output .= '</ul>';

	return $output;
}


/**
 * Render real language URLs from Polylang or WPML; never fake a language URL.
 */
function isg_theme_language_switcher(): void {
    $languages = array();
    if ( function_exists( 'pll_the_languages' ) ) {
        $items = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_current' => 0 ) );
        if ( is_array( $items ) ) {
            foreach ( $items as $language ) {
                if ( empty( $language['url'] ) || empty( $language['slug'] ) || ! isg_theme_is_internal_url( (string) $language['url'] ) ) { continue; }
                $languages[] = array(
                    'code' => strtoupper( (string) $language['slug'] ),
                    'url' => (string) $language['url'],
                    'hreflang' => ! empty( $language['locale'] ) ? strtolower( str_replace( '_', '-', (string) $language['locale'] ) ) : strtolower( (string) $language['slug'] ),
                    'name' => ! empty( $language['name'] ) ? (string) $language['name'] : strtoupper( (string) $language['slug'] ),
                    'active' => ! empty( $language['current_lang'] ),
                );
            }
        }
    } elseif ( has_filter( 'wpml_active_languages' ) ) {
        $items = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'code' ) );
        if ( is_array( $items ) ) {
            foreach ( $items as $language ) {
                if ( empty( $language['url'] ) || empty( $language['language_code'] ) || ! isg_theme_is_internal_url( (string) $language['url'] ) ) { continue; }
                $languages[] = array(
                    'code' => strtoupper( (string) $language['language_code'] ),
                    'url' => (string) $language['url'],
                    'hreflang' => strtolower( str_replace( '_', '-', (string) ( $language['default_locale'] ?? $language['language_code'] ) ) ),
                    'name' => ! empty( $language['native_name'] ) ? (string) $language['native_name'] : strtoupper( (string) $language['language_code'] ),
                    'active' => ! empty( $language['active'] ),
                );
            }
        }
    }

    if ( count( $languages ) > 1 ) {
        echo '<nav class="language-switcher" aria-label="' . esc_attr( isg_theme_t( 'Choose language', 'Выбрать язык' ) ) . '">';
        foreach ( $languages as $language ) {
            $title = $language['name'];
            if ( $language['active'] ) {
                echo '<span class="language-link is-current" aria-current="page" title="' . esc_attr( $title ) . '">' . esc_html( $language['code'] ) . '</span>';
            } else {
                echo '<a class="language-link" href="' . esc_url( $language['url'] ) . '" hreflang="' . esc_attr( $language['hreflang'] ) . '" lang="' . esc_attr( $language['hreflang'] ) . '" title="' . esc_attr( $title ) . '">' . esc_html( $language['code'] ) . '</a>';
            }
        }
        echo '</nav>';
        return;
    }

    $code = 'EN';
    if ( function_exists( 'determine_locale' ) ) {
        $locale = determine_locale();
        $code = strtoupper( substr( (string) $locale, 0, 2 ) );
    }
    echo '<span class="language-link language-link--disabled" title="' . esc_attr( isg_theme_t( 'Install and configure Polylang or WPML to enable translations', 'Подключите и настройте Polylang или WPML для перевода страниц' ) ) . '" aria-label="' . esc_attr( isg_theme_t( 'Current language: ', 'Текущий язык: ' ) . $code ) . '">' . esc_html( $code ) . ' <span aria-hidden="true">⌄</span></span>';
}

/** Mark primary menu's first-level dropdowns as styled mega menus. */
add_filter( 'nav_menu_submenu_css_class', 'isg_theme_primary_mega_menu_class', 10, 3 );
function isg_theme_primary_mega_menu_class( array $classes, $args, int $depth ): array {
    if ( 0 === $depth && is_object( $args ) && ! empty( $args->theme_location ) && 'primary' === $args->theme_location ) {
        $classes[] = 'mega-menu';
    }
    return $classes;
}
