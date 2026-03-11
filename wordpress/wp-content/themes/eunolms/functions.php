<?php
function eunolms_setup() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'align-wide' );
    
    register_nav_menus( array(
        'primary' => __( 'Menú Principal', 'eunolms' ),
        'footer'  => __( 'Menú Footer', 'eunolms' ),
    ) );
}
add_action( 'after_setup_theme', 'eunolms_setup' );

function eunolms_scripts() {
    // Google Fonts
    wp_enqueue_style( 'euno-google-fonts', 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', array(), null );
    // Font Awesome
    wp_enqueue_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', array(), '5.15.4' );
    // Main Style
    wp_enqueue_style( 'eunolms-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version') );
    
    // Lazy Reveal JS (Vanilla, no jQuery)
    wp_enqueue_script( 'eunolms-lazy-reveal', get_theme_file_uri('/assets/js/lazy-reveal.js'), array(), wp_get_theme()->get('Version'), true );
    // Global SPA Navigation
    wp_enqueue_script( 'eunolms-spa-nav', get_theme_file_uri('/assets/js/spa-navigation.js'), array('eunolms-lazy-reveal'), wp_get_theme()->get('Version'), true );
}
add_action( 'wp_enqueue_scripts', 'eunolms_scripts' );
