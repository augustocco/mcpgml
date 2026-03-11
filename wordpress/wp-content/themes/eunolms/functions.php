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
    // Get file modification times for versioning
    $css_file = get_template_directory() . '/css/user-profile.css';
    $css_version = file_exists( $css_file ) ? filemtime( $css_file ) : '2.0.0';
    
    $js_file = get_template_directory() . '/js/user-profile.js';
    $js_version = file_exists( $js_file ) ? filemtime( $js_file ) : '2.0.0';
    
    wp_enqueue_style( 'eunolms-style', get_stylesheet_uri() );
    
    // Enqueue user profile CSS with version
    wp_enqueue_style( 'eunolms-user-profile', get_template_directory_uri() . '/css/user-profile.css', array(), $css_version );
    
    wp_enqueue_script( 'eunolms-header', get_template_directory_uri() . '/js/header.js', array(), '1.0.0', true );
    
    // Enqueue user profile script on profile page with version
    if ( is_page( 'mi-perfil' ) ) {
        wp_enqueue_script( 'eunolms-user-profile', get_template_directory_uri() . '/js/user-profile.js', array('jquery'), $js_version, true );
        wp_localize_script( 'eunolms-user-profile', 'wpApiSettings', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
        ) );
    }
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

function eunolms_get_user_courses_by_status() {
    $user_id = get_current_user_id();
    if ( ! $user_id || ! function_exists('learndash_user_get_enrolled_courses') ) {
        return array(
            'in_progress' => array(),
            'not_started' => array(),
            'completed'   => array(),
        );
    }

    $enrolled_courses_ids = learndash_user_get_enrolled_courses( $user_id );
    $courses_by_status = array(
        'in_progress' => array(),
        'not_started' => array(),
        'completed'   => array(),
    );

    foreach ( $enrolled_courses_ids as $course_id ) {
        $course_status = learndash_course_status( $course_id, $user_id );
        $course_post = get_post( $course_id );
        if( ! $course_post ) continue;

        // Get course progress percentage
        $progress = 0;
        if ( function_exists( 'learndash_get_user_course_progress' ) ) {
            $course_progress = learndash_get_user_course_progress( $user_id, $course_id );
            $progress = isset( $course_progress['percentage'] ) ? $course_progress['percentage'] : 0;
        }

        // Get lesson count
        $lessons_count = 0;
        if ( function_exists( 'learndash_get_lesson_list' ) ) {
            $lessons = learndash_get_lesson_list( $course_id );
            $lessons_count = is_array( $lessons ) ? count( $lessons ) : 0;
        }

        // Get course duration from meta
        $duration = get_post_meta( $course_id, '_duration', true );
        if ( empty( $duration ) ) {
            // Try alternate meta keys
            $duration = get_post_meta( $course_id, 'course_duration', true );
        }
        
        // Format duration if needed
        if ( ! empty( $duration ) && is_numeric( $duration ) ) {
            $hours = floor( $duration / 60 );
            $minutes = $duration % 60;
            if ( $hours > 0 ) {
                $duration = sprintf( '%dh %dm', $hours, $minutes );
            } else {
                $duration = sprintf( '%dm', $minutes );
            }
        }

        // Get enrolled students count
        $students_count = 0;
        if ( function_exists( 'learndash_get_course_users_enrolled_count' ) ) {
            $students_count = learndash_get_course_users_enrolled_count( $course_id );
        } elseif ( function_exists( 'learndash_get_users_for_course' ) ) {
            $students = learndash_get_users_for_course( $course_id, array() );
            $students_count = is_array( $students ) ? count( $students ) : 0;
        }

        $course_data = array(
            'id'             => $course_id,
            'title'          => $course_post->post_title,
            'permalink'      => get_permalink( $course_id ),
            'image'          => get_the_post_thumbnail_url( $course_id, 'medium' ),
            'progress'       => $progress,
            'lessons_count'  => $lessons_count,
            'duration'       => $duration,
            'students_count' => $students_count,
        );

        if ( $course_status === 'completed' ) {
            $courses_by_status['completed'][] = $course_data;
        } elseif ( $course_status === 'in-progress' ) {
            $courses_by_status['in_progress'][] = $course_data;
        } else {
            $courses_by_status['not_started'][] = $course_data;
        }
    }

    return $courses_by_status;
}
add_action( 'template_redirect', 'eunolms_redirect_home_to_profile' );

function eunolms_get_user_stat( $stat ) {
    $user_id = get_current_user_id();
    if ( ! $user_id ) {
        return 0;
    }

    switch ( $stat ) {
        case 'courses':
            // Total de cursos del grupo padre (cliente) del usuario
            if ( function_exists( 'learndash_group_enrolled_courses' ) && function_exists( 'learndash_get_users_group_ids' ) ) {
                $parent_group_id = 0;
                $user_group_ids = learndash_get_users_group_ids( $user_id );

                if ( ! empty( $user_group_ids ) ) {
                    foreach ( $user_group_ids as $group_id ) {
                        $parent_id = (int) wp_get_post_parent_id( $group_id );

                        if ( 0 === $parent_id ) {
                            $parent_group_id = (int) $group_id;
                            break;
                        }

                        if ( $parent_id > 0 ) {
                            $parent_group_id = $parent_id;
                        }
                    }
                }

                if ( $parent_group_id > 0 ) {
                    $group_courses = learndash_group_enrolled_courses( $parent_group_id );
                    return is_array( $group_courses ) ? count( $group_courses ) : 0;
                }
            }

            return 0;

        case 'assignments':
            // Cursos en los que el usuario está inscrito (Asignaciones)
            if ( function_exists('learndash_user_get_enrolled_courses') ) {
                $enrolled_courses = learndash_user_get_enrolled_courses( $user_id );
                return count( $enrolled_courses );
            }
            return 0;

        case 'quizzes':
            // Cuestionarios que el usuario ha intentado
            $user_quiz_meta = get_user_meta( $user_id, '_sfwd-quizzes', true );
            return empty($user_quiz_meta) ? 0 : count($user_quiz_meta);

        case 'groups':
            // Grupos a los que pertenece el usuario
            if ( function_exists('learndash_get_users_group_ids') ) {
                $user_groups = learndash_get_users_group_ids( $user_id );
                return count( $user_groups );
            }
            return 0;

        case 'certificates':
             // Certificados obtenidos por el usuario
             $args = array(
                'post_type' => 'sfwd-certificates',
                'author' => $user_id,
                'posts_per_page' => -1,
                'post_status' => 'publish'
             );
             $query = new WP_Query($args);
             return $query->post_count;

        default:
            return 0;
    }
}

/**
 * Register REST API endpoints for user profile courses
 */
function eunolms_register_rest_routes() {
    register_rest_route( 'eunolms/v1', '/courses/(?P<status>[a-zA-Z0-9-]+)', array(
        'methods'  => 'GET',
        'callback' => 'eunolms_get_courses_by_status_rest',
        'permission_callback' => function() {
            return is_user_logged_in();
        },
    ) );
}
add_action( 'rest_api_init', 'eunolms_register_rest_routes' );

/**
 * REST API callback to get courses by status
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function eunolms_get_courses_by_status_rest( $request ) {
    $status = sanitize_text_field( $request['status'] );
    $user_id = get_current_user_id();
    
    if ( ! $user_id ) {
        return new WP_Error( 'not_logged_in', 'Usuario no autenticado', array( 'status' => 401 ) );
    }

    // Get all courses by status
    $all_courses = eunolms_get_user_courses_by_status();
    
    // Map status from URL to array key
    $status_map = array(
        'en-progreso' => 'in_progress',
        'sin-iniciar' => 'not_started',
        'completados' => 'completed'
    );
    
    $array_key = isset( $status_map[$status] ) ? $status_map[$status] : null;
    
    if ( ! $array_key || ! isset( $all_courses[$array_key] ) ) {
        return new WP_Error( 'invalid_status', 'Estado no válido', array( 'status' => 400 ) );
    }
    
    $courses = $all_courses[$array_key];
    
    return new WP_REST_Response( array(
        'success' => true,
        'data' => array(
            'status' => $status,
            'courses' => $courses,
            'count' => count( $courses )
        )
    ), 200 );
}
