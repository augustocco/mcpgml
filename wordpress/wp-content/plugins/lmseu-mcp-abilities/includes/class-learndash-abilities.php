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
            'meta'                => array( 
                'show_in_rest' => true, 
                'mcp'          => array( 'public' => true ),
                'annotations'  => array( 'readonly' => false ) 
            ),
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
            'meta'                => array( 
                'show_in_rest' => true, 
                'mcp'          => array( 'public' => true ),
                'annotations'  => array( 'readonly' => false ) 
            ),
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
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true ), 'annotations' => array( 'readonly' => false ) ),
        ) );
        // —— Resetear progreso de usuario ——
        wp_register_ability( 'learndash/reset-user-progress', array(
            'label'               => __( 'Resetear Progreso de Usuario', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Elimina el estado de curso completado y/o el progreso de una lección específica para un usuario.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'user_id'   => array( 'type' => 'integer', 'description' => 'ID del usuario' ),
                    'course_id' => array( 'type' => 'integer', 'description' => 'ID del curso a resetear' ),
                    'lesson_id' => array( 'type' => 'integer', 'description' => 'ID de la lección a des-completar (opcional)' ),
                ),
                'required' => array( 'user_id', 'course_id' )
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'reset_user_progress' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true ), 'annotations' => array( 'readonly' => false ) ),
        ) );

        // —— Matricular usuario en curso ——
        wp_register_ability( 'learndash/enroll-user', array(
            'label'               => __( 'Matricular Usuario en Curso', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Inscribe a un usuario en un curso específico.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'user_id'   => array( 'type' => 'integer', 'description' => 'ID del usuario' ),
                    'course_id' => array( 'type' => 'integer', 'description' => 'ID del curso' ),
                ),
                'required' => array( 'user_id', 'course_id' )
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'enroll_user' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true ), 'annotations' => array( 'readonly' => false ) ),
        ) );

        // —— Registrar Tiempo de Estudio ——
        wp_register_ability( 'learndash/track-time', array(
            'label'               => __( 'Registrar Tiempo de Estudio', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Registra el tiempo efectivo de estudio de un usuario en un paso del curso.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'user_id'   => array( 'type' => 'integer' ),
                    'course_id' => array( 'type' => 'integer' ),
                    'step_id'   => array( 'type' => 'integer' ),
                    'seconds'   => array( 'type' => 'integer', 'description' => 'Segundos a incrementar' ),
                ),
                'required' => array( 'user_id', 'course_id', 'step_id', 'seconds' )
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'track_time' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true ), 'annotations' => array( 'readonly' => false ) ),
        ) );

        // —— Obtener Tiempo Acumulado del Curso ——
        wp_register_ability( 'learndash/get-user-course-time', array(
            'label'               => __( 'Obtener Tiempo de Curso', 'lmseu-mcp-abilities' ),
            'category'            => 'learndash',
            'description'         => __( 'Obtiene el tiempo total efectivo de estudio de un usuario en un curso.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'user_id'   => array( 'type' => 'integer' ),
                    'course_id' => array( 'type' => 'integer' ),
                ),
                'required' => array( 'user_id', 'course_id' )
            ),
            'execute_callback'    => array( 'LMSEU_LearnDash_Abilities', 'get_user_course_time' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true ), 'annotations' => array( 'readonly' => false ) ),
        ) );
    }

    /**
     * Obtiene los segundos totales de la tabla personalizada.
     */
    public static function get_user_course_time( $input ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'euno_time_tracking';
        
        $user_id   = (int)$input['user_id'];
        $course_id = (int)$input['course_id'];

        $total_seconds = (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT SUM(seconds) FROM $table_name WHERE user_id = %d AND course_id = %d",
            $user_id, $course_id
        ) );

        $log_file = LMSEU_MCP_ABILITIES_PATH . 'logs/tracking.log';
        $timestamp = date('Y-m-d H:i:s');
        // file_put_contents($log_file, "[$timestamp] GET_TIME: User $user_id, Course $course_id, Total $total_seconds\n", FILE_APPEND);

        return array(
            'seconds' => $total_seconds,
            'hms'     => gmdate("H:i:s", $total_seconds)
        );
    }

    /**
     * Incrementa el tiempo de estudio en la tabla personalizada.
     */
    public static function track_time( $input ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'euno_time_tracking';

        $user_id   = (int)$input['user_id'];
        $course_id = (int)$input['course_id'];
        $step_id   = (int)$input['step_id'];
        $seconds   = (int)$input['seconds'];

        // Seguridad: Un usuario solo puede registrar su propio tiempo
        if ( $user_id !== get_current_user_id() && ! current_user_can( 'administrator' ) && ! current_user_can( 'group_leader' ) ) {
            return array( 'success' => false, 'message' => 'No tienes permiso para actualizar el tiempo de otro usuario.' );
        }

        if ( $seconds <= 0 ) return array( 'success' => true );

        $query = $wpdb->prepare(
            "INSERT INTO $table_name (user_id, course_id, step_id, seconds, last_updated) 
             VALUES (%d, %d, %d, %d, CURRENT_TIMESTAMP)
             ON DUPLICATE KEY UPDATE seconds = seconds + %d, last_updated = CURRENT_TIMESTAMP",
            $user_id, $course_id, $step_id, $seconds, $seconds
        );

        $result = $wpdb->query( $query );

        return array(
            'success' => ( $result !== false ),
            'message' => ( $result !== false ) ? 'Tiempo actualizado.' : 'Error DB: ' . $wpdb->last_error
        );
    }

    public static function enroll_user( $input ) {
        if ( empty( $input['user_id'] ) || empty( $input['course_id'] ) ) {
            return new WP_Error( 'missing_params', 'Faltan parámetros: user_id y course_id son obligatorios.' );
        }

        $user_id = (int) $input['user_id'];
        $course_id = (int) $input['course_id'];

        if ( function_exists( 'ld_update_course_access' ) ) {
            ld_update_course_access( $user_id, $course_id );
            // También forzamos la fecha de acceso para nuestra lógica de 'actually_enrolled'
            update_user_meta( $user_id, 'course_' . $course_id . '_access_from', time() );
            
            return array( 'success' => true, 'message' => "Usuario {$user_id} matriculado con éxito en el curso {$course_id}." );
        }

        return new WP_Error( 'function_missing', 'La función ld_update_course_access no está disponible.' );
    }

    public static function reset_user_progress( $input ) {
        if ( empty( $input['user_id'] ) || empty( $input['course_id'] ) ) {
            return new WP_Error( 'missing_params', 'Faltan parámetros: user_id y course_id son obligatorios.' );
        }

        $user_id = (int) $input['user_id'];
        $course_id = (int) $input['course_id'];
        $lesson_id = ! empty( $input['lesson_id'] ) ? (int) $input['lesson_id'] : 0;

        // Eliminar meta de curso completado
        delete_user_meta( $user_id, 'course_completed_' . $course_id );
        
        // Función nativa para limpiar caché y estados si existe
        if ( function_exists( 'learndash_user_course_complete_remove' ) ) {
            learndash_user_course_complete_remove( $user_id, $course_id );
        }

        $msg = "Se eliminó el estado de curso completado para el usuario {$user_id}. ";

        // Si se pasa una lección, eliminarla del arreglo de progreso
        if ( $lesson_id ) {
            $progress = get_user_meta( $user_id, '_sfwd-course_progress', true );
            if ( is_array( $progress ) && isset( $progress[ $course_id ]['lessons'][ $lesson_id ] ) ) {
                unset( $progress[ $course_id ]['lessons'][ $lesson_id ] );
                update_user_meta( $user_id, '_sfwd-course_progress', $progress );
                
                // Intentar borrar meta individual de la lección completada si existe
                delete_user_meta( $user_id, 'lesson_completed_' . $lesson_id );
                
                $msg .= "Se eliminó el progreso de la lección {$lesson_id}.";
            } else {
                $msg .= "La lección {$lesson_id} no figuraba como completada.";
            }
        }

        return array( 'success' => true, 'message' => $msg );
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
        if ( ! current_user_can( 'administrator' ) && ! current_user_can( 'group_leader' ) ) {
            return new WP_Error( 'rest_forbidden', 'No tienes permisos para acceder a este reporte.', array( 'status' => 403 ) );
        }

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

                // Tiempo Efectivo (EUNO Custom Tracking)
                $table_tracking = $wpdb->prefix . 'euno_time_tracking';
                $effective_seconds = (int) $wpdb->get_var( $wpdb->prepare(
                    "SELECT SUM(seconds) FROM $table_tracking WHERE user_id = %d AND course_id = %d",
                    $user->ID, $course_id
                ) );
                $effective_time_hms = gmdate("H:i:s", $effective_seconds);

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
                    'tiempo_efectivo'        => $effective_time_hms,
                    'completion_time'        => $completion_time,
                    'Username'               => $user->user_login,
                    'First Name'             => $first_name,
                    'Last Name'              => $last_name,
                    'Group(s)'               => $grupos_str,
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
LMSEU_LearnDash_Abilities::register();