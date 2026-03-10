<?php
/**
 * Gestor de contenido LearnDash multi-cliente local.
 * Archivo: class-client-learndash-manager.php
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Client_LearnDash_Manager {

    public static function init() {
        // Filtrar cursos y lecciones de LearnDash en el Admin
        add_action( 'pre_get_posts', array( __CLASS__, 'filter_learndash_content' ) );

        // Forzar el Grupo Padre al crear o actualizar un grupo si hay un cliente activo
        add_filter( 'wp_insert_post_data', array( __CLASS__, 'force_group_parent_to_client' ), 10, 2 );

        // Etiquetar cualquier post de LearnDash con el cliente activo
        add_action( 'save_post', array( __CLASS__, 'tag_learndash_post_with_client' ), 10, 3 );
    }

    /**
     * Guarda el ID del cliente como metadato al crear/editar un post de LearnDash.
     */
    public static function tag_learndash_post_with_client( $post_id, $post, $update ) {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $learndash_types = array( 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-question', 'sfwd-certificates', 'groups' );
        if ( ! in_array( $post->post_type, $learndash_types ) ) {
            return;
        }

        $client_id = LMSEU_Client_Storage_Manager::get_current_client_id();
        if ( $client_id ) {
            // No etiquetarse a sí mismo
            if ( $post->post_type === 'groups' && $post_id == $client_id ) {
                return;
            }
            update_post_meta( $post_id, '_euno_client_id', $client_id );
        }
    }

    /**
     * Asegura que cualquier grupo creado/editado bajo un cliente activo sea asignado como su hijo.
     */
    public static function force_group_parent_to_client( $data, $postarr ) {
        // Solo actuar si estamos guardando un grupo
        if ( $data['post_type'] === 'groups' ) {
            // No intervenir si es un autoguardado o una revisión
            if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
                return $data;
            }

            $client_id = LMSEU_Client_Storage_Manager::get_current_client_id();
            if ( $client_id ) {
                // Si el grupo que estamos editando es el propio cliente principal, no lo anidamos dentro de sí mismo
                if ( isset( $postarr['ID'] ) && $postarr['ID'] == $client_id ) {
                    $data['post_parent'] = 0; // Los clientes (grupos padre) se mantienen en la raíz
                } else {
                    $data['post_parent'] = $client_id; // Forzamos a que sea un sub-grupo (área) del cliente
                }
            }
        }
        return $data;
    }

    /**
     * Filtra los post types de LearnDash en el wp-admin según el cliente activo.
     */
    public static function filter_learndash_content( $query ) {
        // Solo aplicar en el área de administración y a la consulta principal
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        // Obtener la pantalla actual para asegurar que estamos en una lista de posts
        global $pagenow;
        if ( $pagenow !== 'edit.php' ) {
            return;
        }

        $post_type = $query->get( 'post_type' );
        $learndash_types = array( 'groups', 'sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'sfwd-question', 'sfwd-certificates' );

        // Si el post_type no está definido (a veces pasa en consultas raras), no hacemos nada
        if ( empty( $post_type ) || ! in_array( $post_type, $learndash_types ) ) {
            return;
        }

        // Obtener el ID del cliente seleccionado en el switcher (gracias a class-client-storage-manager.php)
        $client_id = LMSEU_Client_Storage_Manager::get_current_client_id();

        if ( $client_id ) {
            // Obtener todos los cursos asociados al Grupo Padre (Cliente)
            $course_ids = array();
            if ( function_exists( 'learndash_group_enrolled_courses' ) ) {
                $course_ids = learndash_group_enrolled_courses( $client_id );
            }

            // Si el cliente no tiene cursos asignados y no estamos viendo grupos o certificados, forzamos a que no muestre nada
            if ( empty( $course_ids ) && ! in_array( $post_type, array('groups', 'sfwd-certificates', 'sfwd-question') ) ) {
                $query->set( 'post__in', array( 0 ) );
                return;
            }

            if ( $post_type === 'groups' ) {
                // Filtrar la lista de grupos: mostrar solo el Cliente y sus Sub-grupos
                $child_groups = get_posts( array(
                    'post_type'      => 'groups',
                    'posts_per_page' => -1,
                    'post_parent'    => $client_id,
                    'fields'         => 'ids'
                ) );
                $allowed_groups = array_merge( array( $client_id ), $child_groups );
                $query->set( 'post__in', $allowed_groups );

            } elseif ( $post_type === 'sfwd-courses' ) {
                // Filtrar la lista de cursos para mostrar solo los de este cliente
                $query->set( 'post__in', $course_ids );
            } else {
                // Para el resto: filtrar por nuestra etiqueta explícita de cliente (_euno_client_id)
                // Y como fallback (para lecciones, temas y cuestionarios viejos), filtrar por course_id.
                $meta_query = $query->get( 'meta_query' );
                if ( ! is_array( $meta_query ) ) {
                    $meta_query = array();
                }

                $client_meta_filter = array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_euno_client_id',
                        'value'   => $client_id,
                        'compare' => '='
                    )
                );

                // Solo estos tipos tienen garantizado tener un course_id históricamente
                if ( in_array( $post_type, array('sfwd-lessons', 'sfwd-topic', 'sfwd-quiz') ) && ! empty( $course_ids ) ) {
                    $client_meta_filter[] = array(
                        'key'     => 'course_id',
                        'value'   => $course_ids,
                        'compare' => 'IN'
                    );
                }

                $meta_query[] = $client_meta_filter;
                $query->set( 'meta_query', $meta_query );
            }
        } else {
            // Contexto: ROOT (0)
            
            if ( $post_type === 'groups' ) {
                // En Root, los grupos que ves son los "Clientes" (Grupos Padre / post_parent = 0)
                $query->set( 'post_parent', 0 );

            } elseif ( function_exists( 'learndash_get_groups' ) && function_exists( 'learndash_group_enrolled_courses' ) ) {
                // Obtener todos los cursos de todos los grupos para EXCLUIRLOS
                // Esto garantiza que el "Root" solo vea cursos huérfanos/públicos
                $all_groups = learndash_get_groups( true ); // true = return IDs
                $all_assigned_courses = array();

                foreach ( $all_groups as $group_id ) {
                    $group_courses = learndash_group_enrolled_courses( $group_id );
                    if ( ! empty( $group_courses ) ) {
                        $all_assigned_courses = array_merge( $all_assigned_courses, $group_courses );
                    }
                }

                $all_assigned_courses = array_unique( $all_assigned_courses );

                if ( $post_type === 'sfwd-courses' ) {
                    if ( ! empty( $all_assigned_courses ) ) {
                        $query->set( 'post__not_in', $all_assigned_courses );
                    }
                } else {
                    $meta_query = $query->get( 'meta_query' );
                    if ( ! is_array( $meta_query ) ) {
                        $meta_query = array();
                    }

                    // Debe NO tener etiqueta de cliente
                    $meta_query[] = array(
                        'key'     => '_euno_client_id',
                        'compare' => 'NOT EXISTS'
                    );

                    // Y además (para lessons/topics/quizzes), no debe pertenecer a los cursos asignados a grupos
                    if ( in_array( $post_type, array('sfwd-lessons', 'sfwd-topic', 'sfwd-quiz') ) && ! empty( $all_assigned_courses ) ) {
                        $meta_query[] = array(
                            'relation' => 'OR',
                            array(
                                'key'     => 'course_id',
                                'value'   => $all_assigned_courses,
                                'compare' => 'NOT IN'
                            ),
                            array(
                                'key'     => 'course_id',
                                'compare' => 'NOT EXISTS'
                            )
                        );
                    }

                    $query->set( 'meta_query', $meta_query );
                }
            }
        }
    }
}