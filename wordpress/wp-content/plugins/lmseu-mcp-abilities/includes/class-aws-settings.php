<?php
/**
 * Página de Configuración AWS para EUNO LMS
 * Settings > EUNO AWS — credenciales para Route53
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_AWS_Settings {

    const OPTION_GROUP  = 'euno_aws_settings';
    const MENU_SLUG     = 'euno-aws-settings';

    /**
     * Opciones registradas
     */
    const OPTIONS = array(
        'euno_aws_access_key'       => 'Access Key ID',
        'euno_aws_secret_key'       => 'Secret Access Key',
        'euno_aws_region'           => 'Región AWS',
        'euno_aws_route53_zone_id'  => 'Route53 Hosted Zone ID',
        'euno_aws_alb_hostname'     => 'Hostname del ALB',
    );

    public static function init() {
        add_action( 'admin_menu',  array( __CLASS__, 'add_menu_page' ) );
        add_action( 'admin_init',  array( __CLASS__, 'register_settings' ) );
    }

    /**
     * Agrega la página bajo Settings
     */
    public static function add_menu_page() {
        add_options_page(
            __( 'EUNO AWS', 'lmseu-mcp-abilities' ),
            __( 'EUNO AWS', 'lmseu-mcp-abilities' ),
            'manage_options',
            self::MENU_SLUG,
            array( __CLASS__, 'render_page' )
        );
    }

    /**
     * Registra todas las opciones con sanitización
     */
    public static function register_settings() {
        foreach ( array_keys( self::OPTIONS ) as $option_name ) {
            register_setting(
                self::OPTION_GROUP,
                $option_name,
                array( 'sanitize_callback' => 'sanitize_text_field' )
            );
        }

        add_settings_section(
            'euno_aws_main',
            __( 'Credenciales AWS', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'section_description' ),
            self::MENU_SLUG
        );

        add_settings_field(
            'euno_aws_access_key',
            __( 'Access Key ID', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'field_text' ),
            self::MENU_SLUG,
            'euno_aws_main',
            array( 'name' => 'euno_aws_access_key', 'type' => 'text' )
        );

        add_settings_field(
            'euno_aws_secret_key',
            __( 'Secret Access Key', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'field_text' ),
            self::MENU_SLUG,
            'euno_aws_main',
            array( 'name' => 'euno_aws_secret_key', 'type' => 'password' )
        );

        add_settings_field(
            'euno_aws_region',
            __( 'Región AWS', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'field_text' ),
            self::MENU_SLUG,
            'euno_aws_main',
            array( 'name' => 'euno_aws_region', 'type' => 'text', 'placeholder' => 'us-east-1' )
        );

        add_settings_field(
            'euno_aws_route53_zone_id',
            __( 'Route53 Hosted Zone ID', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'field_text' ),
            self::MENU_SLUG,
            'euno_aws_main',
            array( 'name' => 'euno_aws_route53_zone_id', 'type' => 'text', 'placeholder' => 'Z1234567890ABC' )
        );

        add_settings_field(
            'euno_aws_alb_hostname',
            __( 'Hostname del ALB', 'lmseu-mcp-abilities' ),
            array( __CLASS__, 'field_text' ),
            self::MENU_SLUG,
            'euno_aws_main',
            array( 'name' => 'euno_aws_alb_hostname', 'type' => 'text', 'placeholder' => 'k8s-xxx.us-east-1.elb.amazonaws.com' )
        );
    }

    /**
     * Descripción de la sección
     */
    public static function section_description() {
        echo '<p>' . esc_html__( 'Configura las credenciales AWS para registrar subdominios automáticamente en Route53.', 'lmseu-mcp-abilities' ) . '</p>';
    }

    /**
     * Renderiza un campo de texto
     *
     * @param array $args Argumentos del campo
     */
    public static function field_text( $args ) {
        $name        = $args['name'];
        $type        = isset( $args['type'] ) ? $args['type'] : 'text';
        $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
        $value       = get_option( $name, '' );

        printf(
            '<input type="%s" name="%s" id="%s" value="%s" placeholder="%s" class="regular-text">',
            esc_attr( $type ),
            esc_attr( $name ),
            esc_attr( $name ),
            esc_attr( $value ),
            esc_attr( $placeholder )
        );
    }

    /**
     * Renderiza la página completa
     */
    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( self::MENU_SLUG );
                submit_button( __( 'Guardar Configuración', 'lmseu-mcp-abilities' ) );
                ?>
            </form>
        </div>
        <?php
    }
}

add_action( 'init', array( 'LMSEU_AWS_Settings', 'init' ) );
