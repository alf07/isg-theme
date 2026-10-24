<?php
/** Admin-editable presentation settings for the ISG demo site. */
defined( 'ABSPATH' ) || exit;
add_action( 'admin_menu', 'isg_site_register_home_settings_page' );
function isg_site_register_home_settings_page(): void {
    add_theme_page( 'Настройки темы ISG', 'Настройки темы ISG', 'manage_options', 'isg-home-settings', 'isg_site_render_home_settings_page' );
}
function isg_site_default_home_sections(): array {
    return array( 'news', 'stats', 'purchases', 'solflare', 'allvend', 'skysend', 'careers', 'company', 'partners' );
}
function isg_site_sanitize_home_sections( $value ): array {
    $allowed = isg_site_default_home_sections();
    if ( ! is_array( $value ) ) { return $allowed; }
    return array_values( array_intersect( $allowed, array_map( 'sanitize_key', $value ) ) );
}
function isg_site_default_home_section_order(): array {
    return array( 'news', 'stats', 'purchases', 'solflare', 'allvend', 'skysend', 'careers', 'company', 'partners' );
}
function isg_site_sanitize_home_section_order( $value ): array {
    $allowed = isg_site_default_home_section_order();
    if ( ! is_array( $value ) ) { return $allowed; }
    $order = array_values( array_intersect( $allowed, array_map( 'sanitize_key', $value ) ) );
    return array_values( array_unique( array_merge( $order, array_diff( $allowed, $order ) ) ) );
}
add_action( 'admin_init', 'isg_site_register_home_settings' );
function isg_site_register_home_settings(): void {
    $text_fields = array(
        'isg_home_eyebrow' => array( 'Надзаголовок главного экрана', 'Inform Systems Group · Innovation for life' ),
        'isg_home_hero_title' => array( 'Заголовок главного экрана', 'Solflare crypto card registration' ),
        'isg_home_hero_lead' => array( 'Описание главного экрана', 'The brief includes Solflare crypto card registration. The customer must confirm ISG’s actual role and the applicable terms before publication.' ),
        'isg_home_hero_cta' => array( 'Текст кнопки главного экрана', 'Service details' ),
        'isg_home_intro' => array( 'Описание компании', 'We develop software and infrastructure solutions for self-service, payments and automation.' ),
        'isg_home_cta_label' => array( 'Текст кнопки в блоке «О компании»', 'Discuss a project' ),
        'isg_footer_motto' => array( 'Подпись в подвале', 'Innovation for life.' ),
        'isg_footer_copyright' => array( 'Текст примечания в подвале', 'Test assignment · author Roman Litvinenko · demo version' ),
    );
    add_settings_section( 'isg_home_main', 'Главная страница', '__return_false', 'isg-home-settings' );
    foreach ( $text_fields as $option => $data ) {
        $is_textarea = in_array( $option, array( 'isg_home_hero_lead', 'isg_home_intro' ), true );
        register_setting( 'isg_home_settings', $option, array( 'sanitize_callback' => $is_textarea ? 'sanitize_textarea_field' : 'sanitize_text_field', 'default' => $data[1] ) );
        add_settings_field( $option, $data[0], 'isg_site_render_text_setting_field', 'isg-home-settings', 'isg_home_main', array( 'label_for' => $option, 'option' => $option, 'default' => $data[1], 'textarea' => $is_textarea ) );
    }
    register_setting( 'isg_home_settings', 'isg_home_sections', array( 'sanitize_callback' => 'isg_site_sanitize_home_sections', 'default' => isg_site_default_home_sections() ) );
    add_settings_field( 'isg_home_sections', 'Видимость секций главной', 'isg_site_render_home_sections_field', 'isg-home-settings', 'isg_home_main' );
    register_setting( 'isg_home_settings', 'isg_home_section_order', array( 'sanitize_callback' => 'isg_site_sanitize_home_section_order', 'default' => isg_site_default_home_section_order() ) );
    add_settings_field( 'isg_home_section_order', 'Порядок секций главной', 'isg_site_render_home_section_order_field', 'isg-home-settings', 'isg_home_main' );
    register_setting( 'isg_home_settings', 'isg_theme_primary_color', array( 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#0166ff' ) );
    register_setting( 'isg_home_settings', 'isg_theme_accent_color', array( 'sanitize_callback' => 'sanitize_hex_color', 'default' => '#19c37d' ) );
    add_settings_field( 'isg_theme_primary_color', 'Основной цвет', 'isg_site_render_color_field', 'isg-home-settings', 'isg_home_main', array( 'option' => 'isg_theme_primary_color', 'default' => '#0166ff' ) );
    add_settings_field( 'isg_theme_accent_color', 'Акцентный цвет', 'isg_site_render_color_field', 'isg-home-settings', 'isg_home_main', array( 'option' => 'isg_theme_accent_color', 'default' => '#19c37d' ) );
}
function isg_site_render_text_setting_field( array $args ): void {
    $option = $args['option']; $value = get_option( $option, $args['default'] );
    if ( ! empty( $args['textarea'] ) ) {
        printf( '<textarea id="%1$s" name="%1$s" rows="3" class="large-text">%2$s</textarea>', esc_attr( $option ), esc_textarea( (string) $value ) );
    } else {
        printf( '<input id="%1$s" class="regular-text" name="%1$s" value="%2$s">', esc_attr( $option ), esc_attr( (string) $value ) );
    }
}
function isg_site_render_color_field( array $args ): void {
    $option = $args['option'];
    printf( '<input type="color" name="%1$s" value="%2$s"> <code>%2$s</code>', esc_attr( $option ), esc_attr( (string) get_option( $option, $args['default'] ) ) );
}
function isg_site_render_home_sections_field(): void {
    $labels = array( 'news' => 'Новости', 'stats' => 'Показатели', 'purchases' => 'Популярные предложения / товары', 'solflare' => 'Solflare', 'allvend' => 'ALLVEND', 'skysend' => 'SkySend', 'careers' => 'Вакансии', 'company' => 'О компании', 'partners' => 'Примеры внедрений' );
    $selected = get_option( 'isg_home_sections', isg_site_default_home_sections() );
    if ( ! is_array( $selected ) ) { $selected = isg_site_default_home_sections(); }
    echo '<fieldset><p class="description">Снимите отметку, чтобы временно скрыть секцию на главной странице.</p>';
    foreach ( $labels as $key => $label ) {
        printf( '<label style="display:block;margin:6px 0"><input type="checkbox" name="isg_home_sections[]" value="%1$s" %2$s> %3$s</label>', esc_attr( $key ), checked( in_array( $key, $selected, true ), true, false ), esc_html( $label ) );
    }
    echo '</fieldset>';
}
function isg_site_render_home_section_order_field(): void {
    $labels = array( 'news' => 'Новости', 'stats' => 'Показатели', 'purchases' => 'Популярные предложения / товары', 'solflare' => 'Solflare', 'allvend' => 'ALLVEND', 'skysend' => 'SkySend', 'careers' => 'Вакансии', 'company' => 'О компании', 'partners' => 'Примеры внедрений' );
    $order = get_option( 'isg_home_section_order', isg_site_default_home_section_order() );
    if ( ! is_array( $order ) ) { $order = isg_site_default_home_section_order(); }
    echo '<p class="description">Перемещайте секции стрелками. Порядок сохраняется после нажатия «Сохранить изменения».</p><ol id="isg-home-section-order" style="max-width:520px">';
    foreach ( $order as $key ) { if ( ! isset( $labels[ $key ] ) ) { continue; }
        echo '<li style="display:flex;align-items:center;gap:10px;margin:8px 0;padding:8px;background:#fff;border:1px solid #ccd0d4"><span style="flex:1">' . esc_html( $labels[ $key ] ) . '</span><input type="hidden" name="isg_home_section_order[]" value="' . esc_attr( $key ) . '"><button type="button" class="button isg-order-up" aria-label="Выше">↑</button><button type="button" class="button isg-order-down" aria-label="Ниже">↓</button></li>';
    }
    echo '</ol><script>(function(){var list=document.getElementById("isg-home-section-order");if(!list)return;list.addEventListener("click",function(e){var b=e.target.closest("button");if(!b)return;var li=b.closest("li");if(b.classList.contains("isg-order-up")&&li.previousElementSibling)list.insertBefore(li,li.previousElementSibling);if(b.classList.contains("isg-order-down")&&li.nextElementSibling)list.insertBefore(li.nextElementSibling,li);});})();</script>';
}
function isg_site_render_home_settings_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) { return; }
    echo '<div class="wrap"><h1>Настройки темы ISG</h1><p>Здесь редактируются основные тексты, видимость секций главной страницы и цвета. Логотип и меню управляются стандартными средствами WordPress в разделе «Внешний вид».</p><form action="options.php" method="post">';
    settings_fields( 'isg_home_settings' ); do_settings_sections( 'isg-home-settings' ); submit_button(); echo '</form></div>';
}
add_action( 'wp_head', 'isg_site_print_presentation_settings', 30 );
function isg_site_print_presentation_settings(): void {
    if ( is_admin() ) { return; }
    $primary = sanitize_hex_color( (string) get_option( 'isg_theme_primary_color', '#0166ff' ) ) ?: '#0166ff';
    $accent = sanitize_hex_color( (string) get_option( 'isg_theme_accent_color', '#19c37d' ) ) ?: '#19c37d';
    echo '<style id="isg-site-presentation-settings">:root{--isg-blue:' . esc_html( $primary ) . ';--isg-green:' . esc_html( $accent ) . ';}';
    if ( is_front_page() ) {
        $selected = get_option( 'isg_home_sections', isg_site_default_home_sections() );
        if ( ! is_array( $selected ) ) { $selected = isg_site_default_home_sections(); }
        $selectors = array( 'news' => '#news', 'stats' => '.stats-section, .stats-note', 'purchases' => '#products', 'solflare' => '#solflare', 'allvend' => '#allvend', 'skysend' => '#skysend', 'careers' => '#careers', 'company' => '#company', 'partners' => '#partners' );
        $order = get_option( 'isg_home_section_order', isg_site_default_home_section_order() );
        if ( ! is_array( $order ) ) { $order = isg_site_default_home_section_order(); }
        echo 'body.home #main-content{display:flex;flex-direction:column}body.home #main-content>.hero-section{order:0}body.home #main-content>section:not(.hero-section){order:50}body.home #main-content>.prototype-note{order:51}';
        foreach ( $selectors as $key => $selector ) {
            if ( ! in_array( $key, $selected, true ) ) { echo esc_html( $selector ) . '{display:none!important;}'; }
            $position = array_search( $key, $order, true );
            if ( false !== $position ) { echo esc_html( $selector ) . '{order:' . (string) ( 100 + $position ) . ';}'; }
        }
    }
    echo '</style>';
}
