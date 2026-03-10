<?php
/**
 * Habilidades de soporte: verificación de usuarios, creación de páginas y gestión de medios.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Support_Abilities {

    public static function register() {
        wp_register_ability( 'support/check-user-exists-by-email', array(
            'label'               => 'Verificar usuario por email',
            'category'            => 'support',
            'description'         => 'Verifica si existe un usuario registrado con el email proporcionado.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'email' => array(
                        'type'        => 'string',
                        'format'      => 'email',
                        'description' => 'Email del usuario a verificar.',
                    ),
                ),
                'required'   => array( 'email' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'exists'       => array( 'type' => 'boolean' ),
                    'masked_email' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'exists' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'check_user_exists' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
                'annotations'  => array( 'readonly' => true ),
            ),
        ) );

        wp_register_ability( 'support/create-page', array(
            'label'               => 'Crear página en WordPress',
            'category'            => 'support',
            'description'         => 'Crea una nueva página en WordPress con el título y contenido especificados.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'title'   => array( 'type' => 'string', 'description' => 'Título de la página.' ),
                    'content' => array( 'type' => 'string', 'description' => 'Contenido de la página.' ),
                    'status'  => array( 'type' => 'string', 'default' => 'publish', 'description' => 'Estado de la página (publish, draft, etc).' ),
                ),
                'required'   => array( 'title', 'content' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'id'  => array( 'type' => 'integer' ),
                    'url' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'id', 'url' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'create_page' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
                'annotations'  => array( 'readonly' => false, 'destructive' => false, 'idempotent' => false ),
            ),
        ) );

        wp_register_ability( 'support/enable-elementor', array(
            'label'               => 'Habilitar Elementor en una página',
            'category'            => 'support',
            'description'         => 'Habilita el editor Elementor para una página específica.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array( 'type' => 'integer', 'description' => 'ID del post/página.' ),
                ),
                'required'   => array( 'post_id' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'success' => array( 'type' => 'boolean' ),
                    'message' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'success', 'message' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'enable_elementor' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
                'annotations'  => array( 'readonly' => false, 'destructive' => false, 'idempotent' => false ),
            ),
        ) );

        wp_register_ability( 'support/upload-default-image', array(
            'label'               => 'Subir imagen por defecto (Base64)',
            'category'            => 'support',
            'description'         => 'Sube una imagen al sistema de medios desde una cadena Base64 y la marca como imagen por defecto.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'filename' => array( 'type' => 'string', 'default' => 'default-course.webp' ),
                    'base64'   => array( 'type' => 'string', 'description' => 'Contenido de la imagen en base64.' ),
                ),
                'required'   => array( 'base64' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'id'  => array( 'type' => 'integer' ),
                    'url' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'id', 'url' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'upload_default_image' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'annotations'  => array( 'readonly' => false, 'destructive' => false, 'idempotent' => false ),
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
            ),
        ) );

        wp_register_ability( 'support/upload-user-avatar', array(
            'label'               => 'Subir Avatar de Usuario',
            'category'            => 'support',
            'description'         => 'Sube una imagen de perfil para un usuario específico y la guarda en su meta data.',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'user_id'  => array( 'type' => 'integer', 'description' => 'ID del usuario.' ),
                    'filename' => array( 'type' => 'string', 'description' => 'Nombre del archivo (opcional).' ),
                    'base64'   => array( 'type' => 'string', 'description' => 'Contenido de la imagen en base64.' ),
                ),
                'required'   => array( 'user_id', 'base64' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'url' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'url' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'upload_user_avatar' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
                'annotations'  => array( 'readonly' => false, 'destructive' => false, 'idempotent' => false ),
            ),
        ) );

        wp_register_ability( 'support/execute-php', array(
            'label'               => 'Exec PHP',
            'category'            => 'support',
            'description'         => 'Ejecuta código PHP y retorna el resultado (para debugging).',
            'input_schema'        => array(
                'type'       => 'object',
                'properties' => array(
                    'code' => array( 'type' => 'string', 'description' => 'Código PHP a ejecutar.' ),
                ),
                'required'   => array( 'code' ),
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'output' => array( 'type' => 'string' ),
                ),
                'required'   => array( 'output' ),
            ),
            'execute_callback'    => array( 'LMSEU_Support_Abilities', 'execute_php' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
                'annotations'  => array( 'readonly' => false, 'destructive' => true, 'idempotent' => false ),
            ),
        ) );
    }

    public static function execute_php( $input ) {
        ob_start();
        try {
            eval( $input['code'] );
        } catch ( Exception $e ) {
            echo 'Error: ' . $e->getMessage();
        }
        return array( 'output' => ob_get_clean() );
    }

    public static function upload_user_avatar( $input ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $user_id = intval( $input['user_id'] );
        $filename = !empty($input['filename']) ? sanitize_file_name($input['filename']) : 'avatar-' . $user_id . '.jpg';
        $base64_data = preg_replace('#^data:image/\w+;base64,#i', '', $input['base64']);

        $image_data = base64_decode( $base64_data );
        if ( ! $image_data ) return new WP_Error( 'invalid_base64', 'Datos base64 inválidos.' );

        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        file_put_contents( $file_path, $image_data );

        $file_array = array( 'name' => $filename, 'tmp_name' => $file_path );
        $id = media_handle_sideload( $file_array, 0 );

        if ( is_wp_error( $id ) ) {
            @unlink( $file_path );
            return $id;
        }

        update_user_meta( $user_id, '_euno_custom_avatar', $id );
        return array( 'url' => wp_get_attachment_url( $id ) );
    }

    public static function check_user_exists( $input ) {
        $email = sanitize_email( $input['email'] );
        $user = get_user_by( 'email', $email );
        $exists = (bool) $user;
        return array( 'exists' => $exists, 'masked_email' => $exists ? substr($email, 0, 2) . '***@' . explode('@', $email)[1] : $email );
    }

    public static function create_page( $input ) {
        $post_id = wp_insert_post( array(
            'post_title'   => sanitize_text_field( $input['title'] ),
            'post_content' => $input['content'],
            'post_status'  => isset($input['status']) ? $input['status'] : 'publish',
            'post_type'    => 'page',
        ) );
        if ( is_wp_error( $post_id ) ) return $post_id;
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $post_id, '_elementor_template', 'elementor_header_footer' );
        return array( 'id' => $post_id, 'url' => get_permalink( $post_id ) );
    }

    public static function enable_elementor( $input ) {
        $post_id = intval( $input['post_id'] );
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $post_id, '_elementor_template', 'elementor_header_footer' );
        return array( 'success' => true, 'message' => 'Elementor habilitado.' );
    }

    public static function upload_default_image( $input ) {
        require_once( ABSPATH . 'wp-admin/includes/image.php' );
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        require_once( ABSPATH . 'wp-admin/includes/media.php' );

        $filename = sanitize_file_name( $input['filename'] );
        $base64_data = $input['base64'];

        $image_data = base64_decode( $base64_data );
        if ( ! $image_data ) {
            return new WP_Error( 'invalid_base64', 'Datos base64 inválidos.', array( 'status' => 400 ) );
        }

        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;
        file_put_contents( $file_path, $image_data );

        $file_array = array(
            'name'     => $filename,
            'tmp_name' => $file_path,
        );

        $id = media_handle_sideload( $file_array, 0 );

        if ( is_wp_error( $id ) ) {
            @unlink( $file_path );
            return $id;
        }

        update_option( 'euno_default_course_image_id', $id );

        return array(
            'id'  => $id,
            'url' => wp_get_attachment_url( $id ),
        );
    }
}