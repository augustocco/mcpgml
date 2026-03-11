<?php
/**
 * Meta Box para Configuración de Branding de Clientes
 * Solo visible para Grupos Padre (Clientes) en el admin
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Client_Branding_Meta_Box {

    /**
     * ID del meta box
     */
    const META_BOX_ID = 'euno_client_branding_meta_box';

    /**
     * Hook de inicialización
     */
    public static function init() {
        add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_box' ), 20 );
        add_action( 'save_post', array( __CLASS__, 'save_meta_box_data' ), 10, 2 );
    }

    /**
     * Agrega el meta box solo a grupos padre (Clientes)
     */
    public static function add_meta_box() {
        // Solo agregar meta box en la edición de grupos de LearnDash
        add_meta_box(
            self::META_BOX_ID,
            __( 'Configuración de Branding del Cliente', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'render_meta_box' ),
            'groups',
            'side',
            'high'
        );
    }

    /**
     * Renderiza el contenido del meta box
     *
     * @param WP_Post $post Objeto del post actual
     */
    public static function render_meta_box( $post ) {
        // Verificar si es un cliente (grupo padre)
        $parent_id = wp_get_post_parent_id( $post->ID );
        
        // Si es un grupo padre o el post está siendo creado como grupo padre
        $is_client_group = ( $post->post_parent == 0 );
        
        if ( ! $is_client_group ) {
            ?>
            <div class="notice notice-info">
                <p><?php _e( 'Esta configuración es solo para Grupos Padre (Clientes). Los Grupos Hijo heredarán estos valores.', 'lmseu-mcp-abilities' ); ?></p>
            </div>
            <?php
            return;
        }

        // Obtener valores actuales
        $isotype_url = get_post_meta( $post->ID, '_euno_client_isotype', true );
        $color_primary = get_post_meta( $post->ID, '_euno_color_primary', true );
        $color_secondary = get_post_meta( $post->ID, '_euno_color_secondary', true );
        $color_tertiary = get_post_meta( $post->ID, '_euno_color_tertiary', true );

        // Valores por defecto si no existen
        $color_primary = $color_primary ?: '#2563eb';
        $color_secondary = $color_secondary ?: '#64748b';
        $color_tertiary = $color_tertiary ?: '#0f172a';

        // Generar nonce para seguridad
        wp_nonce_field( self::META_BOX_ID, self::META_BOX_ID . '_nonce' );

        ?>
        <div class="euno-branding-meta-box">
            <!-- Isotipo -->
            <div class="euno-field">
                <label for="euno_isotype_url">
                    <?php _e( 'Isotipo (Icono/Logo pequeño)', 'lmseu-mcp-abilities' ); ?>
                    <span class="description"><?php _e( 'Imagen pequeña usada en el header (opcional)', 'lmseu-mcp-abilities' ); ?></span>
                </label>
                
                <div class="euno-file-upload">
                    <input type="hidden" name="euno_isotype_url" id="euno_isotype_url" value="<?php echo esc_url( $isotype_url ); ?>">
                    <input type="text" id="euno_isotype_preview" class="regular-text" placeholder="<?php _e( 'URL del isotipo', 'lmseu-mcp-abilities' ); ?>" value="<?php echo esc_attr( $isotype_url ); ?>" readonly>
                    
                    <button type="button" class="button euno-upload-button" id="euno_upload_isotype">
                        <?php _e( 'Subir Imagen', 'lmseu-mcp-abilities' ); ?>
                    </button>
                    
                    <button type="button" class="button euno-remove-button" id="euno_remove_isotype" style="display: <?php echo ! empty( $isotype_url ) ? 'inline-block' : 'none'; ?>">
                        <?php _e( 'Eliminar', 'lmseu-mcp-abilities' ); ?>
                    </button>
                </div>
                
                <?php if ( $isotype_url ) : ?>
                    <div class="euno-image-preview">
                        <img src="<?php echo esc_url( $isotype_url ); ?>" alt="<?php _e( 'Previsualización del isotipo', 'lmseu-mcp-abilities' ); ?>">
                    </div>
                <?php endif; ?>
            </div>

            <hr>

            <!-- Colores Corporativos -->
            <div class="euno-field">
                <label><?php _e( 'Colores Corporativos', 'lmseu-mcp-abilities' ); ?></label>
                <p class="description"><?php _e( 'Estos colores se aplicarán en todo el sitio del cliente.', 'lmseu-mcp-abilities' ); ?></p>

                <!-- Color Principal -->
                <div class="euno-color-field">
                    <label for="euno_color_primary">
                        <strong><?php _e( 'Principal', 'lmseu-mcp-abilities' ); ?></strong>
                    </label>
                    <input type="color" name="euno_color_primary" id="euno_color_primary" value="<?php echo esc_attr( $color_primary ); ?>">
                    <span class="euno-color-value"><?php echo esc_html( $color_primary ); ?></span>
                </div>

                <!-- Color Secundario -->
                <div class="euno-color-field">
                    <label for="euno_color_secondary">
                        <strong><?php _e( 'Secundario', 'lmseu-mcp-abilities' ); ?></strong>
                    </label>
                    <input type="color" name="euno_color_secondary" id="euno_color_secondary" value="<?php echo esc_attr( $color_secondary ); ?>">
                    <span class="euno-color-value"><?php echo esc_html( $color_secondary ); ?></span>
                </div>

                <!-- Color Terciario -->
                <div class="euno-color-field">
                    <label for="euno_color_tertiary">
                        <strong><?php _e( 'Terciario', 'lmseu-mcp-abilities' ); ?></strong>
                    </label>
                    <input type="color" name="euno_color_tertiary" id="euno_color_tertiary" value="<?php echo esc_attr( $color_tertiary ); ?>">
                    <span class="euno-color-value"><?php echo esc_html( $color_tertiary ); ?></span>
                </div>

                <!-- Preview de colores -->
                <div class="euno-color-preview" style="background: linear-gradient(135deg, <?php echo esc_attr( $color_primary ); ?> 33%, <?php echo esc_attr( $color_secondary ); ?> 33%, <?php echo esc_attr( $color_secondary ); ?> 66%, <?php echo esc_attr( $color_tertiary ); ?> 66%); height: 30px; border-radius: 4px; margin-top: 10px;"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Guarda los datos del meta box
     *
     * @param int $post_id ID del post
     * @param WP_Post $post Objeto del post
     */
    public static function save_meta_box_data( $post_id, $post ) {
        // Verificar nonce
        if ( ! isset( $_POST[ self::META_BOX_ID . '_nonce' ] ) || ! wp_verify_nonce( $_POST[ self::META_BOX_ID . '_nonce' ], self::META_BOX_ID ) ) {
            return;
        }

        // Verificar permisos
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Verificar autoguardado
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        // Verificar que el post_type es groups
        if ( get_post_type( $post_id ) !== 'groups' ) {
            return;
        }

        // Solo procesar si es un cliente (grupo padre)
        $is_client_group = ( $post->post_parent == 0 );
        if ( ! $is_client_group ) {
            return;
        }

        // Guardar isotipo
        if ( isset( $_POST['euno_isotype_url'] ) ) {
            $isotype_url = sanitize_text_field( $_POST['euno_isotype_url'] );
            if ( empty( $isotype_url ) ) {
                delete_post_meta( $post_id, '_euno_client_isotype' );
            } else {
                update_post_meta( $post_id, '_euno_client_isotype', $isotype_url );
            }
        }

        // Guardar colores
        $colors = array(
            'primary' => '',
            'secondary' => '',
            'tertiary' => ''
        );

        if ( isset( $_POST['euno_color_primary'] ) ) {
            $colors['primary'] = sanitize_hex_color( $_POST['euno_color_primary'] );
        }
        if ( isset( $_POST['euno_color_secondary'] ) ) {
            $colors['secondary'] = sanitize_hex_color( $_POST['euno_color_secondary'] );
        }
        if ( isset( $_POST['euno_color_tertiary'] ) ) {
            $colors['tertiary'] = sanitize_hex_color( $_POST['euno_color_tertiary'] );
        }

        LMSEU_Client_Branding_Manager::save_client_colors( $post_id, $colors );
    }

    /**
     * Añade scripts y estilos para el meta box
     */
    public static function enqueue_scripts() {
        wp_enqueue_style( 'euno-branding-meta-box', plugins_url( 'css/euno-branding-meta-box.css', __FILE__ ) );
        wp_enqueue_script( 'euno-branding-meta-box', plugins_url( 'js/euno-branding-meta-box.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
        
        wp_localize_script( 'euno-branding-meta-box', 'eunoBrandingMetaBox', array(
            'title' => __( 'Seleccionar Imagen', 'lmseu-mcp-abilities' ),
            'button' => __( 'Usar esta imagen', 'lmseu-mcp-abilities' ),
            'cancel' => __( 'Cancelar', 'lmseu-mcp-abilities' ),
            'uploadError' => __( 'Error al subir la imagen. Por favor, inténtelo de nuevo.', 'lmseu-mcp-abilities' )
        ) );
    }
}

// Enqueue scripts
add_action( 'admin_enqueue_scripts', array( 'LMSEU_Client_Branding_Meta_Box', 'enqueue_scripts' ), 20 );

// Inicializar
add_action( 'init', array( 'LMSEU_Client_Branding_Meta_Box', 'init' ) );