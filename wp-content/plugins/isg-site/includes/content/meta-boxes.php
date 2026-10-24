<?php
/** Small, native WordPress meta boxes for fields used by the theme. */
defined( 'ABSPATH' ) || exit;
add_action( 'add_meta_boxes', 'isg_site_add_meta_boxes' );
function isg_site_add_meta_boxes(): void {
    add_meta_box( 'isg_content_details', 'Данные для сайта', 'isg_site_render_content_meta_box', array( 'isg_solution', 'isg_service', 'isg_vacancy', 'isg_partner' ), 'normal', 'default' );
}
function isg_site_render_content_meta_box( WP_Post $post ): void {
    wp_nonce_field( 'isg_site_save_content_meta', 'isg_site_content_nonce' );
    $fields = array(
        'isg_subtitle' => array( 'Краткий подзаголовок', 'text' ),
        'isg_cta_label' => array( 'Текст основной кнопки', 'text' ),
        'isg_cta_url' => array( 'URL основной кнопки', 'url' ),
        'isg_demo_url' => array( 'URL демо-материала на этом сайте', 'url' ),
        'isg_price_note' => array( 'Цена или примечание к условиям (необязательно)', 'text' ),
    );
    if ( 'isg_vacancy' === $post->post_type ) {
        $fields['isg_salary'] = array( 'Зарплата (только подтверждённое значение)', 'text' );
        $fields['isg_location'] = array( 'Формат / место работы', 'text' );
    }
    if ( 'isg_partner' === $post->post_type ) {
        $fields['isg_case_client'] = array( 'Организация / название кейса', 'text' );
        $fields['isg_case_year'] = array( 'Год внедрения (если подтверждён)', 'text' );
    }
    echo '<table class="form-table"><tbody>';
    foreach ( $fields as $key => $field ) {
        $value = get_post_meta( $post->ID, $key, true );
        printf( '<tr><th><label for="%1$s">%2$s</label></th><td><input class="regular-text" id="%1$s" name="%1$s" type="%3$s" value="%4$s"></td></tr>', esc_attr( $key ), esc_html( $field[0] ), esc_attr( $field[1] ), esc_attr( $value ) );
    }
    echo '</tbody></table><p class="description">Оставляйте поля пустыми, если данных нет или они не подтверждены. В URL-полях разрешены только внутренние ссылки на этот сайт. Основной текст и изображение редактируются стандартными средствами WordPress.</p>';
}
add_action( 'save_post', 'isg_site_save_content_meta' );
function isg_site_save_content_meta( int $post_id ): void {
    if ( ! isset( $_POST['isg_site_content_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['isg_site_content_nonce'] ) ), 'isg_site_save_content_meta' ) ) { return; }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return; }
    if ( ! current_user_can( 'edit_post', $post_id ) ) { return; }
    $post_type = get_post_type( $post_id );
    if ( ! in_array( $post_type, array( 'isg_solution', 'isg_service', 'isg_vacancy', 'isg_partner' ), true ) ) { return; }
    $keys = array( 'isg_subtitle', 'isg_cta_label', 'isg_cta_url', 'isg_demo_url', 'isg_price_note', 'isg_salary', 'isg_location', 'isg_case_client', 'isg_case_year' );
    foreach ( $keys as $key ) {
        if ( ! isset( $_POST[ $key ] ) ) { continue; }
        $raw = sanitize_text_field( wp_unslash( $_POST[ $key ] ) );
        $value = str_ends_with( $key, '_url' ) ? ( isg_site_is_internal_url( $raw ) ? esc_url_raw( $raw ) : '' ) : $raw;
        update_post_meta( $post_id, $key, $value );
    }
}


/** Allow only internal links in content CTA fields. */
function isg_site_is_internal_url( string $url ): bool {
    $url = trim( html_entity_decode( $url, ENT_QUOTES, 'UTF-8' ) );
    if ( '' === $url ) { return false; }
    $parts = wp_parse_url( $url );
    if ( false === $parts ) { return false; }
    $scheme = strtolower( (string) ( $parts['scheme'] ?? '' ) );
    $host = strtolower( rtrim( (string) ( $parts['host'] ?? '' ), '.' ) );
    if ( '' === $host ) { return '' === $scheme; }
    if ( ! in_array( $scheme, array( '', 'http', 'https' ), true ) ) { return false; }
    $normalize = static function ( string $value ): string { return preg_replace( '/^www\./i', '', strtolower( rtrim( $value, '.' ) ) ); };
    $hosts = array( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ), (string) wp_parse_url( site_url( '/' ), PHP_URL_HOST ) );
    foreach ( array_filter( $hosts ) as $site_host ) {
        if ( $normalize( $host ) === $normalize( $site_host ) ) { return true; }
    }
    return false;
}
