<?php
/** Site-specific content types. WooCommerce owns purchasable products and orders. */
defined( 'ABSPATH' ) || exit;
add_action( 'init', 'isg_site_register_post_types' );
function isg_site_register_post_types(): void {
    $types = array(
        'isg_solution' => array( 'Решения', 'Решение', 'Решения', 'dashicons-screenoptions', true, 'products' ),
        'isg_service'  => array( 'Услуги', 'Услуга', 'Услуги', 'dashicons-hammer', true, 'services' ),
        'isg_vacancy'  => array( 'Вакансии', 'Вакансия', 'Вакансии', 'dashicons-id-alt', true, 'careers' ),
        'isg_partner'  => array( 'Партнёры и кейсы', 'Партнёр / кейс', 'Партнёры и кейсы', 'dashicons-groups', true, 'cases' ),
    );
    foreach ( $types as $slug => $data ) {
        register_post_type( $slug, array(
            'labels' => array(
                'name' => $data[0], 'singular_name' => $data[1],
                'add_new_item' => 'Добавить запись',
                'edit_item' => 'Редактировать запись',
                'view_items' => 'Посмотреть записи',
                'search_items' => 'Найти записи',
            ),
            'public' => $data[4], 'show_in_rest' => true, 'has_archive' => true,
            'menu_icon' => $data[3], 'rewrite' => array( 'slug' => $data[5], 'with_front' => false ),
            'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes' ),
        ) );
    }
}
