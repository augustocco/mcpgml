<?php
/**
 * Plugin Name: LMSEU MCP Abilities
 * Description: Registro de habilidades personalizadas para el MCP Adapter de WordPress.
 * Version: 2.8.0
 * Author: Augusto César Cañola Ortiz
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'LMSEU_MCP_ABILITIES_PATH', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, 'lmseu_mcp_abilities_activate' );
function lmseu_mcp_abilities_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'euno_time_tracking';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        course_id bigint(20) NOT NULL,
        step_id bigint(20) NOT NULL,
        seconds int(11) DEFAULT 0,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY user_course_step (user_id, course_id, step_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-student-profile.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-enrolled-courses.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-reports-dashboard.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-global-filters.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-client-storage-manager.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-client-learndash-manager.php';

add_action( 'plugins_loaded', array( 'LMSEU_Client_Storage_Manager', 'init' ) );
add_action( 'plugins_loaded', array( 'LMSEU_Client_LearnDash_Manager', 'init' ) );

add_action( 'init', function() {
    if ( ! get_page_by_path( 'ayuda' ) ) {
        wp_insert_post([
            'post_title'   => 'Ayuda',
            'post_name'    => 'ayuda',
            'post_content' => '<h1>Centro de Ayuda</h1><p>Esta página se encuentra bajo construcción para ofrecerte el mejor soporte.</p>',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ]);
    }

    if ( ! get_page_by_path( 'informes' ) ) {
        wp_insert_post([
            'post_title'   => 'Informes',
            'post_name'    => 'informes',
            'post_content' => '[euno_reports_dashboard]',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        ]);
    }

    $page_cursos = get_page_by_path('cursos');
    if ( $page_cursos && strpos($page_cursos->post_content, '[euno_enrolled_courses]') === false ) {
        wp_update_post([
            'ID' => $page_cursos->ID,
            'post_content' => '[euno_enrolled_courses]'
        ]);
    }

    $menu_name = 'Menu Principal EUNO';
    if ( ! wp_get_nav_menu_object( $menu_name ) ) {
        $menu_id = wp_create_nav_menu( $menu_name );
        wp_update_nav_menu_item( $menu_id, 0, [ 'menu-item-title' => 'Inicio', 'menu-item-url' => home_url( '/mi-perfil/' ), 'menu-item-status' => 'publish' ] );
        wp_update_nav_menu_item( $menu_id, 0, [ 'menu-item-title' => 'Cursos', 'menu-item-url' => home_url( '/cursos/' ), 'menu-item-status' => 'publish' ] );
        wp_update_nav_menu_item( $menu_id, 0, [ 'menu-item-title' => 'Ayuda', 'menu-item-url' => home_url( '/ayuda/' ), 'menu-item-status' => 'publish' ] );
    }
});

add_action( 'wp_abilities_api_categories_init', function() {
    if ( ! function_exists( 'wp_register_ability_category' ) ) return;
    
    wp_register_ability_category( 'support', [
        'label'       => 'Soporte',
        'description' => 'Habilidades de soporte técnico y utilidades.'
    ] );
    
    wp_register_ability_category( 'learndash', [
        'label'       => 'LearnDash',
        'description' => 'Habilidades relacionadas con el LMS LearnDash.'
    ] );
    
    wp_register_ability_category( 'wordpress', [
        'label'       => 'WordPress',
        'description' => 'Habilidades nativas de WordPress.'
    ] );
}, 10 );

add_action( 'wp_abilities_api_init', function() {
    if ( ! function_exists( 'wp_register_ability' ) ) return;

    $learndash_file = plugin_dir_path( __FILE__ ) . 'includes/class-learndash-abilities.php';
    if ( file_exists( $learndash_file ) ) {
        require_once $learndash_file;
        LMSEU_LearnDash_Abilities::register();
    }

    $support_class_file = plugin_dir_path( __FILE__ ) . 'includes/class-support-abilities.php';
    if ( file_exists( $support_class_file ) ) {
        require_once $support_class_file;
        LMSEU_Support_Abilities::register();
    }

    $wordpress_file = plugin_dir_path( __FILE__ ) . 'includes/class-wordpress-abilities.php';
    if ( file_exists( $wordpress_file ) ) {
        require_once $wordpress_file;
        LMSEU_WordPress_Abilities::register();
    }
}, 10 );
