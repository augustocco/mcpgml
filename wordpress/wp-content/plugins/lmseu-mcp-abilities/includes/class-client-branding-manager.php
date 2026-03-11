<?php
/**
 * Gestor de Branding Multicliente
 * Maneja logos, isotipos y colores corporativos por cliente (grupo padre)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Client_Branding_Manager {

    /**
     * Colores por defecto de EUNO
     */
    const DEFAULT_COLORS = array(
        'primary'    => '#2563eb',
        'secondary'  => '#64748b',
        'tertiary'   => '#0f172a'
    );

    /**
     * Nombre de los metacampos de branding
     */
    const META_KEYS = array(
        'isotype_url' => '_euno_client_isotype',
        'color_primary' => '_euno_color_primary',
        'color_secondary' => '_euno_color_secondary',
        'color_tertiary' => '_euno_color_tertiary'
    );

    /**
     * Obtiene el branding completo de un cliente basado en el usuario
     *
     * @param int $user_id ID del usuario
     * @return array Información de branding con logo, isotipo y colores
     */
    public static function get_client_branding( $user_id ) {
        $parent_group_id = self::get_client_parent_group( $user_id );

        if ( ! $parent_group_id ) {
            return self::get_default_branding();
        }

        // Obtener el logo como Featured Image
        $logo_url = get_the_post_thumbnail_url( $parent_group_id, 'full' );

        // Si no hay featured image, usar logo por defecto
        if ( ! $logo_url ) {
            $logo_url = 'https://eks12.lmseunoconsulting.com/wp-content/uploads/2026/03/euno2025.png';
        }

        // Obtener isotipo del metacampo
        $isotype_url = get_post_meta( $parent_group_id, self::META_KEYS['isotype_url'], true );
        if ( ! $isotype_url ) {
            $isotype_url = ''; // Sin isotipo
        }

        // Obtener colores del metacampo o usar default
        $branding = array(
            'logo_url' => $logo_url,
            'isotype_url' => $isotype_url,
            'color_primary' => get_post_meta( $parent_group_id, self::META_KEYS['color_primary'], true ) ?: self::DEFAULT_COLORS['primary'],
            'color_secondary' => get_post_meta( $parent_group_id, self::META_KEYS['color_secondary'], true ) ?: self::DEFAULT_COLORS['secondary'],
            'color_tertiary' => get_post_meta( $parent_group_id, self::META_KEYS['color_tertiary'], true ) ?: self::DEFAULT_COLORS['tertiary'],
            'group_id' => $parent_group_id
        );

        return $branding;
    }

    /**
     * Obtiene el ID del grupo padre (cliente) del usuario
     *
     * @param int $user_id ID del usuario
     * @return int|false ID del grupo padre o false si no existe
     */
    public static function get_client_parent_group( $user_id ) {
        if ( ! function_exists( 'learndash_get_users_group_ids' ) ) {
            return false;
        }

        $user_groups = learndash_get_users_group_ids( $user_id );

        if ( empty( $user_groups ) ) {
            return false;
        }

        // Buscar el grupo padre (donde post_parent = 0)
        foreach ( $user_groups as $group_id ) {
            $parent_id = wp_get_post_parent_id( $group_id );
            if ( $parent_id == 0 ) {
                return $group_id; // Es un grupo padre
            }
        }

        // Si el usuario está en grupos hijo pero no en padre, buscar uno de sus padres
        foreach ( $user_groups as $group_id ) {
            $parent_id = wp_get_post_parent_id( $group_id );
            if ( $parent_id > 0 ) {
                return $parent_id; // Retorna el padre
            }
        }

        return false;
    }

    /**
     * Obtiene el branding por defecto de EUNO
     *
     * @return array Información de branding default
     */
    public static function get_default_branding() {
        return array(
            'logo_url' => 'https://eks12.lmseunoconsulting.com/wp-content/uploads/2026/03/euno2025.png',
            'isotype_url' => '',
            'color_primary' => self::DEFAULT_COLORS['primary'],
            'color_secondary' => self::DEFAULT_COLORS['secondary'],
            'color_tertiary' => self::DEFAULT_COLORS['tertiary'],
            'group_id' => 0
        );
    }

    /**
     * Guarda los colores corporativos de un grupo
     *
     * @param int $group_id ID del grupo
     * @param array $colors Array con colores (primary, secondary, tertiary)
     * @return bool True si se guardó correctamente
     */
    public static function save_client_colors( $group_id, $colors ) {
        if ( ! is_array( $colors ) ) {
            return false;
        }

        $updated = false;
        
        if ( isset( $colors['primary'] ) && self::is_valid_color( $colors['primary'] ) ) {
            update_post_meta( $group_id, self::META_KEYS['color_primary'], $colors['primary'] );
            $updated = true;
        }

        if ( isset( $colors['secondary'] ) && self::is_valid_color( $colors['secondary'] ) ) {
            update_post_meta( $group_id, self::META_KEYS['color_secondary'], $colors['secondary'] );
            $updated = true;
        }

        if ( isset( $colors['tertiary'] ) && self::is_valid_color( $colors['tertiary'] ) ) {
            update_post_meta( $group_id, self::META_KEYS['color_tertiary'], $colors['tertiary'] );
            $updated = true;
        }

        return $updated;
    }

    /**
     * Valida si un string es un color hex válido
     *
     * @param string $color Color a validar
     * @return bool True si es válido
     */
    private static function is_valid_color( $color ) {
        return preg_match( '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $color );
    }

    /**
     * Verifica si un grupo es un cliente (grupo padre)
     *
     * @param int $group_id ID del grupo
     * @return bool True si es un grupo padre
     */
    public static function is_client_group( $group_id ) {
        return $group_id > 0 && wp_get_post_parent_id( $group_id ) == 0;
    }

    /**
     * Obtiene todos los clientes activos (grupos padre)
     *
     * @return array Array con IDs de grupos padre
     */
    public static function get_all_clients() {
        if ( ! function_exists( 'learndash_get_groups' ) ) {
            return array();
        }

        $all_groups = learndash_get_groups( true ); // true = return IDs
        
        $clients = array();
        foreach ( $all_groups as $group_id ) {
            if ( self::is_client_group( $group_id ) ) {
                $clients[] = $group_id;
            }
        }

        return $clients;
    }
}

// Inicializar la clase
add_action( 'init', function() {
    LMSEU_Client_Branding_Manager::init();
}, 10 );