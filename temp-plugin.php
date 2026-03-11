<?php
/**
 * Plugin Name: Create My Profile Page
 * Description: A temporary plugin to create the 'Mi Perfil' page.
 * Version: 1.0
 * Author: Trae
 */

function create_my_profile_page() {
    // Check if the page already exists
    $page = get_page_by_path( 'mi-perfil' );

    if ( ! $page ) {
        // Create post object
        $my_post = array(
            'post_title'    => wp_strip_all_tags( 'Mi Perfil' ),
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1,
            'post_type'     => 'page',
            'page_template' => 'page-mi-perfil.php'
        );

        // Insert the post into the database
        wp_insert_post( $my_post );
    }
}

// Register the activation hook
register_activation_hook( __FILE__, 'create_my_profile_page' );
