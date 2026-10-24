<?php
/** Editorial grouping for solutions and services. */
defined( 'ABSPATH' ) || exit;
add_action( 'init', 'isg_site_register_taxonomies' );
function isg_site_register_taxonomies(): void {
    register_taxonomy( 'isg_solution_group', array( 'isg_solution' ), array(
        'labels' => array( 'name' => 'Группы решений', 'singular_name' => 'Группа решений', 'menu_name' => 'Группы решений' ),
        'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
        'rewrite' => array( 'slug' => 'solution-group', 'with_front' => false ),
    ) );
    register_taxonomy( 'isg_service_group', array( 'isg_service' ), array(
        'labels' => array( 'name' => 'Группы услуг', 'singular_name' => 'Группа услуг', 'menu_name' => 'Группы услуг' ),
        'public' => true, 'show_in_rest' => true, 'hierarchical' => true,
        'rewrite' => array( 'slug' => 'service-group', 'with_front' => false ),
    ) );
}
