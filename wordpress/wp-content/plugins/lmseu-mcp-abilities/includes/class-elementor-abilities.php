<?php
/**
 * Habilidades de Elementor: Construcción de layouts.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Elementor_Abilities {

    public static function register() {
        wp_register_ability( 'elementor/build-layout', array(
            'label'               => __( 'Construir Layout con Elementor', 'lmseu-mcp-abilities' ),
            'category'            => 'elementor',
            'description'         => __( 'Reemplaza el contenido de un post/lección con una estructura nativa de widgets de Elementor (Títulos, Textos, Imágenes, Videos).', 'lmseu-mcp-abilities' ),
            'input_schema'        => array(
                'type'                 => 'object',
                'properties'           => array(
                    'post_id' => array(
                        'type'        => 'integer',
                        'description' => __( 'ID del post, página o lección a modificar.', 'lmseu-mcp-abilities' ),
                    ),
                    'widgets' => array(
                        'type'        => 'array',
                        'description' => __( 'Lista de widgets a añadir en orden.', 'lmseu-mcp-abilities' ),
                        'items'       => array(
                            'type' => 'object',
                            'properties' => array(
                                'type' => array(
                                    'type' => 'string',
                                    'description' => 'Tipo de widget: heading, text-editor, image, video, divider, spacer.',
                                ),
                                'settings' => array(
                                    'type' => 'object',
                                    'description' => 'Configuración específica. Ej: {title: "Hola"} para heading, {editor: "<p>Texto</p>"} para text-editor, {youtube_url: "url"} para video, {url: "url_imagen"} para image.',
                                )
                            ),
                            'required' => array('type')
                        )
                    )
                ),
                'required'             => array( 'post_id', 'widgets' ),
                'additionalProperties' => false,
            ),
            'output_schema'       => array(
                'type'       => 'object',
                'properties' => array(
                    'post_id' => array( 'type' => 'integer' ),
                    'success' => array( 'type' => 'boolean' ),
                ),
                'required'   => array( 'post_id', 'success' ),
            ),
            'execute_callback'    => array( 'LMSEU_Elementor_Abilities', 'build_layout' ),
            'permission_callback' => '__return_true',
            'meta'                => array(
                'show_in_rest' => true,
                'annotations'  => array( 'readonly' => false, 'destructive' => true, 'idempotent' => false ),
                'mcp'          => array( 'public' => true, 'type' => 'tool' ),
            ),
        ) );
    }

    public static function build_layout( $input ) {
        if ( empty( $input['post_id'] ) || ! intval( $input['post_id'] ) ) {
            return new WP_Error( 'invalid_post', __( 'ID de post inválido.', 'lmseu-mcp-abilities' ), array( 'status' => 400 ) );
        }

        $post_id = intval( $input['post_id'] );
        $widgets_input = isset($input['widgets']) && is_array($input['widgets']) ? $input['widgets'] : array();

        // Generar IDs únicos que requiere Elementor
        $section_id = substr( md5( wp_rand() ), 0, 7 );
        $column_id = substr( md5( wp_rand() ), 0, 7 );

        $elements = array();

        foreach ($widgets_input as $widget) {
            if (empty($widget['type'])) continue;
            
            $widget_id = substr( md5( wp_rand() ), 0, 7 );
            $type = $widget['type'];
            $settings = isset($widget['settings']) && is_array($widget['settings']) ? $widget['settings'] : array();

            // Mapeo simplificado para que la IA no tenga que conocer los internals complejos de Elementor
            $elementor_settings = array();
            
            if ( $type === 'heading' ) {
                $elementor_settings['title'] = isset($settings['title']) ? $settings['title'] : '';
                if (isset($settings['header_size'])) $elementor_settings['header_size'] = $settings['header_size'];
                if (isset($settings['align'])) $elementor_settings['align'] = $settings['align'];
            } elseif ( $type === 'text-editor' ) {
                $elementor_settings['editor'] = isset($settings['editor']) ? $settings['editor'] : '';
            } elseif ( $type === 'video' ) {
                $elementor_settings['youtube_url'] = isset($settings['youtube_url']) ? $settings['youtube_url'] : '';
                $elementor_settings['video_type'] = 'youtube';
            } elseif ( $type === 'image' ) {
                $elementor_settings['image'] = array(
                    'url' => isset($settings['url']) ? $settings['url'] : '',
                    'id' => ''
                );
            } else {
                // Permitir otros widgets pasando la configuración directamente
                $elementor_settings = $settings;
            }

            $elements[] = array(
                'id' => $widget_id,
                'elType' => 'widget',
                'isInner' => false,
                'isLocked' => false,
                'settings' => $elementor_settings,
                'widgetType' => $type,
            );
        }

        // Estructura base de Elementor: Section > Column > Widgets
        $elementor_data = array(
            array(
                'id' => $section_id,
                'elType' => 'section',
                'isInner' => false,
                'isLocked' => false,
                'settings' => array(),
                'elements' => array(
                    array(
                        'id' => $column_id,
                        'elType' => 'column',
                        'isInner' => false,
                        'isLocked' => false,
                        'settings' => array(
                            '_column_size' => 100,
                            '_inline_size' => null
                        ),
                        'elements' => $elements
                    )
                )
            )
        );

        // Forzar a WordPress a usar Elementor para este post
        update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
        update_post_meta( $post_id, '_elementor_template_type', 'wp-post' );
        
        if (!get_post_meta($post_id, '_elementor_version', true)) {
            update_post_meta( $post_id, '_elementor_version', '3.0.0' ); 
        }

        // Guardar usando la API de Elementor para compilar HTML y resetear cachés
        if ( class_exists( '\Elementor\Plugin' ) ) {
            $document = \Elementor\Plugin::$instance->documents->get( $post_id );
            if ( $document ) {
                $document->save( [
                    'elements' => $elementor_data,
                    'settings' => []
                ] );
            } else {
                $json_data = wp_slash( wp_json_encode( $elementor_data ) );
                update_post_meta( $post_id, '_elementor_data', $json_data );
            }
            \Elementor\Plugin::$instance->files_manager->clear_cache();
        } else {
            $json_data = wp_slash( wp_json_encode( $elementor_data ) );
            update_post_meta( $post_id, '_elementor_data', $json_data );
            delete_post_meta( $post_id, '_elementor_css' );
        }

        return array(
            'post_id' => $post_id,
            'success' => true
        );
    }
}
