<?php
/**
 * Maneja el branding dinámico basado en grupos de LearnDash.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Branding {

    /**
     * Obtiene la URL del logo de la empresa (Grupo Padre) del usuario actual.
     * 
     * @return string URL del logo.
     */
    public static function get_company_logo_url() {
        // Logo por defecto (EUNO)
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $default_logo = $custom_logo_id ? wp_get_attachment_image_url( $custom_logo_id, 'full' ) : 'https://eks10.lmseunoconsulting.com/wp-content/uploads/2026/03/euno2025-2048x614-1.png';

        if ( ! is_user_logged_in() ) {
            return $default_logo;
        }

        $user_id = get_current_user_id();

        // 1. Obtener grupos del usuario
        if ( ! function_exists( 'learndash_get_users_group_ids' ) ) {
            return $default_logo;
        }

        $group_ids = learndash_get_users_group_ids( $user_id );

        if ( empty( $group_ids ) ) {
            return $default_logo;
        }

        // 2. Buscar el logo en la jerarquía (Empresa = Grupo Padre)
        foreach ( $group_ids as $group_id ) {
            $company_id = self::get_root_group_id( $group_id );
            
            if ( has_post_thumbnail( $company_id ) ) {
                return get_the_post_thumbnail_url( $company_id, 'full' );
            }
        }

        return $default_logo;
    }

    /**
     * Obtiene la lista de IDs de cursos en los que el usuario está REALMENTE matriculado.
     * 
     * @param int $user_id ID del usuario.
     * @return array Lista de IDs de cursos matriculados.
     */
    public static function get_enrolled_course_ids( $user_id ) {
        if ( empty( $user_id ) ) return [];
        
        $all_allowed = self::get_filtered_user_course_ids( $user_id );
        $actually_enrolled = [];

        foreach ( $all_allowed as $cid ) {
            // Un usuario está matriculado si:
            // 1. Tiene acceso explícito (meta course_access_from)
            // 2. Es administrador
            if ( user_can( $user_id, 'administrator' ) || sfwd_lms_has_access( $cid, $user_id ) ) {
                // LearnDash a veces da acceso a 'open' automáticamente, 
                // pero queremos que el usuario haga clic en "Asignar" primero.
                // Verificamos si el curso es abierto.
                $price_type = learndash_get_setting( $cid, 'course_price_type' );
                if ( 'open' === $price_type ) {
                    // Para cursos abiertos, verificamos si hay actividad o si el usuario
                    // ya ha "aceptado" el curso (podemos usar un meta personalizado o simplemente verificar si ya empezó)
                    $has_started = get_user_meta( $user_id, 'course_' . $cid . '_access_from', true );
                    if ( $has_started ) {
                        $actually_enrolled[] = $cid;
                    }
                } else {
                    $actually_enrolled[] = $cid;
                }
            }
        }

        return $actually_enrolled;
    }

    /**
     * Escala en la jerarquía de grupos hasta encontrar el padre raíz.
     *
     * @param int $group_id ID del grupo inicial.
     * @return int ID del grupo raíz.
     */
    public static function get_root_group_id( $group_id ) {
        $parent_id = get_post_field( 'post_parent', $group_id );
        if ( ! empty( $parent_id ) && intval( $parent_id ) > 0 ) {
            return self::get_root_group_id( $parent_id );
        }

        return $group_id;
    }

    /**
     * Obtiene la lista de IDs de cursos permitidos para un usuario según su empresa.
     * 
     * @param int $user_id ID del usuario.
     * @return array Lista de IDs de cursos.
     */
    public static function get_filtered_user_course_ids( $user_id ) {
        if ( empty( $user_id ) ) return [];

        // Los administradores ven todo
        if ( user_can( $user_id, 'administrator' ) ) {
            return function_exists('learndash_user_get_enrolled_courses') ? learndash_user_get_enrolled_courses( $user_id ) : [];
        }

        // 1. Obtener grupos a los que pertenece el usuario directamente
        $user_group_ids = function_exists('learndash_get_users_group_ids') ? learndash_get_users_group_ids( $user_id ) : [];

        if ( empty( $user_group_ids ) ) {
            // Si no tiene grupo, no ve nada (estricto multicliente)
            return [];
        }

        $allowed_courses = [];

        foreach ( $user_group_ids as $gid ) {
            // A. CURSOS DIRECTOS: Cualquier curso asignado al grupo donde está el usuario (siempre visible)
            if ( function_exists('learndash_group_enrolled_courses') ) {
                $direct_courses = learndash_group_enrolled_courses( $gid );
                if ( is_array( $direct_courses ) ) {
                    $allowed_courses = array_merge( $allowed_courses, $direct_courses );
                }
            }

            // B. HERENCIA DEL PADRE (SOLO ABIERTOS):
            $root_id = self::get_root_group_id( $gid );
            
            // Si el usuario no pertenece directamente al grupo raíz, solo heredará los "Abiertos"
            if ( $root_id !== $gid && ! in_array( $root_id, $user_group_ids ) ) {
                if ( function_exists('learndash_group_enrolled_courses') ) {
                    $root_courses = learndash_group_enrolled_courses( $root_id );
                    if ( is_array( $root_courses ) ) {
                        foreach ( $root_courses as $cid ) {
                            $price_type = learndash_get_setting( $cid, 'course_price_type' );
                            if ( 'open' === $price_type ) {
                                $allowed_courses[] = $cid;
                            }
                        }
                    }
                }
            }
        }

        return array_unique( $allowed_courses );
    }
}
