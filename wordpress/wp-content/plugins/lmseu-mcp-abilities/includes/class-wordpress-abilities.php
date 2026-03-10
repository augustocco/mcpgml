<?php
/**
 * Habilidades de WordPress: usuarios, posts, etc.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_WordPress_Abilities {

    public static function register() {

        wp_register_ability( 'wordpress/get-user-count', array(
            'label'               => __( 'Obtener cantidad de usuarios registrados', 'lmseu-mcp-abilities' ),
            'category'            => 'wordpress',
            'description'         => __( 'Obtiene el total de usuarios registrados en WordPress, con desglose por rol.', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'role' => array(
                        'type'        => 'string',
                        'description' => __( 'Opcional: Filtrar por rol específico (subscriber, contributor, author, editor, administrator).', 'lmseu-mcp-abilities' ),
                    ),
                ),
                'additionalProperties' => false,
            ),
            'output_schema'       => array(
                'type'                 => 'object',
                'properties'           => array(
                    'total'   => array( 'type' => 'integer' ),
                    'by_role' => array(
                        'type'                 => 'object',
                        'additionalProperties' => array( 'type' => 'integer' ),
                    ),
                    'message' => array( 'type' => 'string' ),
                ),
                'required'             => array( 'total', 'message' ),
                'additionalProperties' => false,
            ),
            'execute_callback'    => array( 'LMSEU_WordPress_Abilities', 'get_user_count' ),
            'permission_callback' => '__return_true',
            'meta'                => array( 'show_in_rest' => true, 'mcp' => array( 'public' => true, 'type' => 'tool' ), 'annotations' => array( 'readonly' => false ) ),
        ) );
    }

    public static function get_user_count( $input ) {
        $role = isset( $input['role'] ) ? sanitize_text_field( $input['role'] ) : '';

        $args = array();
        if ( ! empty( $role ) ) {
            $args['role'] = $role;
        }

        $user_count = count_users();

        if ( ! empty( $role ) ) {
            $total = isset( $user_count['avail_roles'][ $role ] ) ? $user_count['avail_roles'][ $role ] : 0;
            return array(
                'total' => $total,
                'role' => $role,
                'message' => sprintf( 'Hay %d usuarios con el rol %s', $total, $role )
            );
        }

        return array(
            'total' => $user_count['total_users'],
            'by_role' => $user_count['avail_roles'],
            'message' => sprintf( 'Hay %d usuarios registrados en total', $user_count['total_users'] )
        );
    }
}
