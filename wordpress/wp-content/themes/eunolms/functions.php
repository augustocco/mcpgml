<?php

function eunolms_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'custom-logo' );
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'style',
        'script',
    ) );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'custom-background' );
    add_theme_support( 'custom-header' );
    
    register_nav_menus( array(
        'primary' => __( 'Menú Principal', 'eunolms' ),
    ) );
}
add_action( 'after_setup_theme', 'eunolms_setup' );

function eunolms_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Barra Lateral', 'eunolms' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Widgets de la barra lateral', 'eunolms' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
    
    register_sidebar( array(
        'name'          => __( 'Pie de Página', 'eunolms' ),
        'id'            => 'footer-1',
        'description'   => __( 'Widgets del pie de página', 'eunolms' ),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'eunolms_widgets_init' );

function eunolms_scripts() {
    wp_enqueue_style( 'eunolms-style', get_stylesheet_uri() );
    wp_enqueue_style( 'eunolms-user-profile', get_template_directory_uri() . '/css/user-profile.css' );
    wp_enqueue_script( 'eunolms-header', get_template_directory_uri() . '/js/header.js', array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'eunolms_scripts' );

function eunolms_excerpt_length( $length = 20 ) {
    return 20;
}
add_filter( 'excerpt_length', 'eunolms_excerpt_length' );

function eunolms_excerpt_more( $more ) {
    return '... <a href="' . get_permalink() . '">' . __( 'Leer más', 'eunolms' ) . '</a>';
}
add_filter( 'excerpt_more', 'eunolms_excerpt_more' );

function eunolms_redirect_home_to_profile() {
    if ( is_front_page() && is_user_logged_in() ) {
        wp_redirect( home_url( '/mi-perfil/' ) );
        exit;
    }
}
add_action( 'template_redirect', 'eunolms_redirect_home_to_profile' );

function eunolms_get_user_stat( $stat ) {
    $user_id = get_current_user_id();
    // Esta es una función de ejemplo. Necesitarás reemplazarla con tu propia lógica para obtener las estadísticas.
    switch ( $stat ) {
        case 'courses':
            return 3; // Reemplazar con la lógica real
        case 'assignments':
            return 2; // Reemplazar con la lógica real
        case 'quizzes':
            return 0; // Reemplazar con la lógica real
        case 'groups':
            return 0; // Reemplazar con la lógica real
        case 'certificates':
            return 2; // Reemplazar con la lógica real
        default:
            return 0;
    }
}
