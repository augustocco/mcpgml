<?php
/**
 * Habilidades de LearnDash: cursos, lecciones, etc.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_LearnDash_Abilities {

    public static function register() {

        // —— Obtener cursos ——
        wp_register_ability( 'learndash/get-courses', array(
            'label'               => __( 'Obtener cursos de LearnDash', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Obtiene una lista de los cursos disponibles en LearnDash.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'limit' => array(
                        'type'        => 'integer',
                        'description' => __( 'Número máximo de cursos a devolver.', 'lmseu-mcp-abilities' ),
                        'default'     => 10,
                    ),
                ),
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'get_courses' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true, 'type' => 'tool' ), 'annotations' => array( 'readonly' => false ) ),
        ) );

        // —— Reporte de datos de curso por usuario ——
        wp_register_ability( 'learndash/get-user-course-report', array(
            'label'               => __( 'Exportar datos de cursos de usuarios', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Genera un reporte detallado del progreso de los usuarios en los cursos.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'limit'  => array( 'type' => 'integer', 'default' => 5000 ),
                    'offset' => array( 'type' => 'integer', 'default' => 0 ),
                ),
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'get_user_course_report' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true, 'type' => 'tool' ), 'annotations' => array( 'readonly' => false ) ),
        ) );

        // —— Crear lecciones ——
        wp_register_ability( 'learndash/create-lesson', array(
            'label'               => __( 'Crear Lección de LearnDash', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Crea una nueva lección y la asigna a un curso.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'course_id' => array( 'type' => 'integer', 'description' => 'ID del curso al que pertenece la lección' ),
                    'title'     => array( 'type' => 'string', 'description' => 'Título de la lección' ),
                    'content'   => array( 'type' => 'string', 'description' => 'Contenido de la lección' ),
                ),
                'required' => array( 'course_id', 'title' )
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'create_lesson' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true, 'type' => 'tool' ), 'annotations' => array( 'readonly' => false ) ),
        ) );
    }

    public static function create_lesson( $input ) {
        if ( empty( $input['course_id'] ) || empty( $input['title'] ) ) {
            return new WP_Error( 'missing_params', 'Faltan parámetros: course_id y title son obligatorios.' );
        }

        $lesson_id = wp_insert_post( array(
            'post_type'    => 'sfwd-lessons',
            'post_title'   => sanitize_text_field( $input['title'] ),
            'post_content' => isset( $input['content'] ) ? wp_kses_post( $input['content'] ) : '',
            'post_status'  => 'publish',
            'post_author'  => 1
        ) );

        if ( is_wp_error( $lesson_id ) ) {
            return $lesson_id;
        }

        learndash_update_setting( $lesson_id, 'course', intval( $input['course_id'] ) );
        update_post_meta( $lesson_id, 'course_id', intval( $input['course_id'] ) );

        // Agregar la lección a los Course Steps
        if ( function_exists('learndash_course_add_child_to_parent') ) {
            learndash_course_add_child_to_parent( intval( $input['course_id'] ), $lesson_id, intval( $input['course_id'] ) );
        } else {
            // Fallback para versiones antiguas
            $course_steps_object = learndash_get_course_steps_object( intval( $input['course_id'] ) );
            if ( $course_steps_object ) {
                $course_steps_object->set_step_to_course_legacy( $lesson_id );
            }
        }

        return array(
            'lesson_id' => $lesson_id,
            'course_id' => $input['course_id'],
            'title'     => $input['title'],
            'message'   => 'Lección creada exitosamente'
        );
    }

    public static function get_courses( $input ) {
        $limit = isset( $input['limit'] ) ? intval( $input['limit'] ) : 10;
        $args = array( 'post_type' => 'sfwd-courses', 'posts_per_page' => $limit, 'post_status' => 'publish' );
        $query = new WP_Query( $args );
        $courses = array();
        if ( $query->have_posts() ) {
            foreach ( $query->posts as $post ) {
                $courses[] = array( 'id' => $post->ID, 'title' => $post->post_title, 'url' => get_permalink( $post->ID ) );
            }
        }
        wp_reset_postdata();
        return $courses;
    }

    public static function get_user_course_report( $input ) {
        global $wpdb;
        $limit = isset( $input['limit'] ) ? intval( $input['limit'] ) : 5000;
        $offset = isset( $input['offset'] ) ? intval( $input['offset'] ) : 0;

        $users = get_users( array( 'number' => $limit, 'offset' => $offset ) );
        $report = array();

        foreach ( $users as $user ) {
            if ( ! function_exists( 'learndash_user_get_enrolled_courses' ) ) continue;
            $course_ids = learndash_user_get_enrolled_courses( $user->ID );
            if ( empty( $course_ids ) ) continue;

            // Datos de Perfil / Meta
            $first_name = get_user_meta($user->ID, 'first_name', true);
            $last_name = get_user_meta($user->ID, 'last_name', true);
            
            // Grupos
            $user_groups = array();
            if ( function_exists( 'learndash_get_users_group_ids' ) ) {
                $group_ids = learndash_get_users_group_ids( $user->ID );
                foreach ( $group_ids as $gid ) { $user_groups[] = get_the_title( $gid ); }
            }
            $grupos_str = implode( ', ', $user_groups );

            // Last Login
            $last_login_raw = get_user_meta( $user->ID, 'learndash-last-login', true ) ?: get_user_meta( $user->ID, 'last_login', true );
            $last_login = $last_login_raw ? (is_numeric($last_login_raw) ? date('Y-m-d H:i:s', intval($last_login_raw)) : $last_login_raw) : '';

            // Engagement Total
            $activity_table = $wpdb->prefix . 'learndash_user_activity';
            $total_logins = (int) $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM $activity_table WHERE user_id = %d AND activity_type IN ('login', 'access', 'course')",
                $user->ID
            ) ) ?: 1;

            foreach ( $course_ids as $course_id ) {
                $course = get_post( $course_id );
                if ( ! $course ) continue;

                $pasos_completados = function_exists('learndash_course_get_completed_steps') ? learndash_course_get_completed_steps($user->ID, $course_id) : 0;
                $pasos_totales     = function_exists('learndash_get_course_steps_count') ? learndash_get_course_steps_count($course_id) : 0;

                $status_raw = function_exists('learndash_course_status') ? learndash_course_status($course_id, $user->ID) : 'not-started';
                $curso_completado = (in_array(strtolower($status_raw), array('completado', 'completed', 'terminado', 'finalizado', 'yes'))) ? 'yes' : 'no';

                // Tiempos y Fechas
                $started_ts = get_user_meta($user->ID, 'course_' . $course_id . '_access_from', true);
                $started_on = $started_ts ? date('Y-m-d H:i:s', intval($started_ts)) : '';
                
                $completed_ts = get_user_meta($user->ID, 'course_completed_' . $course_id, true);
                $completado_el = $completed_ts ? date('Y-m-d H:i:s', intval($completed_ts)) : '';

                // Tiempo Total (LearnDash Meta)
                $total_time_seconds = (int) get_user_meta($user->ID, 'course_total_time_on_' . $course_id, true);
                $total_time_hms = gmdate("H:i:s", $total_time_seconds);

                // Tiempo hasta completar
                $completion_time = '';
                if ($started_ts && $completed_ts) {
                    $diff = intval($completed_ts) - intval($started_ts);
                    $completion_time = ($diff > 0) ? gmdate("H:i:s", $diff) : '00:00:00';
                }

                // Ultimo paso
                $last_step_id = (int) get_user_meta($user->ID, 'last_step_id_' . $course_id, true);
                $last_step_type = $last_step_id ? get_post_type($last_step_id) : '';
                $last_step_title = $last_step_id ? get_the_title($last_step_id) : '';

                $report[] = array(
                    'id_de_usuario'          => $user->ID,
                    'nombre'                 => $user->display_name,
                    'email'                  => $user->user_email,
                    'id_del_curso'           => $course_id,
                    'titulo_del_curso'       => $course->post_title,
                    'pasos_completados'      => $pasos_completados,
                    'pasos_totales'          => $pasos_totales,
                    'curso_completado'       => $curso_completado,
                    'curso_completado_el'    => $completado_el,
                    'total_time'             => $total_time_hms,
                    'completion_time'        => $completion_time,
                    'Username'               => $user->user_login,
                    'First Name'             => $first_name,
                    'Last Name'              => $last_name,
                    'Group(s)'               => $grupos_str,
                    'cargo'                  => $cargo,
                    'ciudad'                 => $ciudad,
                    'punto_de_venta'         => $pdv,
                    'celular'                => $celular,
                    'course_started_on'      => $started_on,
                    'course_total_time_on'   => $total_time_hms,
                    'course_last_step_id'    => $last_step_id,
                    'course_last_step_type'  => $last_step_type,
                    'course_last_step_title' => $last_step_title,
                    'last_login_date'        => $last_login,
                    'anio'                   => $started_on ? date('Y', strtotime($started_on)) : date('Y'),
                    'mes'                    => $started_on ? date('m', strtotime($started_on)) : date('m'),
                    'total_logins'           => $total_logins,
                    'grupos_del_usuario'     => $grupos_str, // Para filtros
                );
            }
        }
        return $report;
    }
}

add_action( 'wp_abilities_api_init', function() {
    LMSEU_LearnDash_Abilities::register();
}, 20 );