<?php
/**
 * Detector de Subdominios de Clientes EUNO LMS
 *
 * Detecta el subdominio activo en cada petición, carga el branding del
 * cliente correspondiente en la página de login y gestiona redirecciones
 * post-login al perfil del alumno.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Subdomain_Detector {

    /**
     * Duración del caché de subdominio → grupo (en segundos)
     */
    const CACHE_TTL = 300; // 5 minutos

    /**
     * Dominio base del sitio
     */
    const BASE_DOMAIN = 'lmseunoconsulting.com';

    /**
     * ID del grupo detectado para esta petición (null = sin subdominio de cliente)
     *
     * @var int|null
     */
    private static $group_id = null;

    /**
     * Subdominio detectado (null = ninguno)
     *
     * @var string|null
     */
    private static $active_subdomain = null;

    /**
     * Registra todos los hooks necesarios
     */
    public static function init() {
        // Detectar subdominio lo más temprano posible
        add_action( 'init', array( __CLASS__, 'detect_subdomain' ), 1 );

        // Branding en página de login
        add_action( 'login_head',       array( __CLASS__, 'inject_login_branding' ) );
        add_filter( 'login_headerurl',  array( __CLASS__, 'login_logo_url' ) );

        // Redirección post-login
        add_filter( 'login_redirect',   array( __CLASS__, 'handle_login_redirect' ), 10, 3 );

        // Ajuste de URLs para subdominios activos
        add_filter( 'login_url',  array( __CLASS__, 'filter_login_url' ), 10, 3 );
    }

    /**
     * Detecta el subdominio activo en la petición actual y carga el grupo asociado
     */
    public static function detect_subdomain() {
        $http_host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';

        // Extraer prefijo: host = "bancolombia.lmseunoconsulting.com" → "bancolombia"
        if ( ! preg_match( '/^([a-z0-9][a-z0-9\-]{0,61}[a-z0-9]?)\.' . preg_quote( self::BASE_DOMAIN, '/' ) . '$/', $http_host, $matches ) ) {
            return; // No es un subdominio válido o es el dominio principal
        }

        $subdomain = $matches[1];

        // Ignorar "www" y el subdominio raíz "eks12"
        if ( in_array( $subdomain, array( 'www', 'eks12' ), true ) ) {
            return;
        }

        self::$active_subdomain = $subdomain;
        self::$group_id         = self::get_group_id_for_subdomain( $subdomain );
    }

    /**
     * Busca el grupo padre cuyo meta _euno_client_subdomain coincide con el subdominio.
     * Usa transient como caché.
     *
     * @param string $subdomain
     * @return int|null ID del grupo, o null si no existe
     */
    private static function get_group_id_for_subdomain( $subdomain ) {
        $cache_key = 'euno_subdomain_' . $subdomain;
        $cached    = get_transient( $cache_key );

        if ( false !== $cached ) {
            return ( $cached === 'none' ) ? null : (int) $cached;
        }

        global $wpdb;
        $group_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key = '_euno_client_subdomain'
                   AND meta_value = %s
                 LIMIT 1",
                $subdomain
            )
        );

        if ( $group_id ) {
            set_transient( $cache_key, (int) $group_id, self::CACHE_TTL );
            return (int) $group_id;
        }

        set_transient( $cache_key, 'none', self::CACHE_TTL );
        return null;
    }

    /**
     * Devuelve el ID del grupo activo (null si no hay subdominio de cliente)
     *
     * @return int|null
     */
    public static function get_active_group_id() {
        return self::$group_id;
    }

    /**
     * Devuelve el subdominio activo (null si no aplica)
     *
     * @return string|null
     */
    public static function get_active_subdomain() {
        return self::$active_subdomain;
    }

    /**
     * Inyecta CSS de branding del cliente en la página de login de WordPress
     */
    public static function inject_login_branding() {
        if ( ! self::$group_id ) {
            return;
        }

        $branding = LMSEU_Client_Branding_Manager::get_client_branding_by_group( self::$group_id );

        if ( empty( $branding ) ) {
            return;
        }

        $logo_url    = ! empty( $branding['logo_url'] )    ? esc_url( $branding['logo_url'] )    : '';
        $cp          = ! empty( $branding['color_primary'] )   ? esc_attr( $branding['color_primary'] )   : '#2563eb';
        $cs          = ! empty( $branding['color_secondary'] ) ? esc_attr( $branding['color_secondary'] ) : '#64748b';

        echo '<style>
body.login {
    background: #f8fafc;
}
body.login #login h1 a {
    background-image: url("' . $logo_url . '") !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    width: 240px !important;
    height: 80px !important;
}
body.login .wp-login-logo a {
    background-image: url("' . $logo_url . '") !important;
    background-size: contain !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    width: 240px !important;
    height: 80px !important;
}
body.login input[type="submit"],
body.login .button-primary {
    background: ' . $cp . ' !important;
    border-color: ' . $cp . ' !important;
}
body.login input[type="submit"]:hover,
body.login .button-primary:hover {
    background: ' . $cs . ' !important;
    border-color: ' . $cs . ' !important;
}
body.login a {
    color: ' . $cp . ' !important;
}
body.login a:hover {
    color: ' . $cs . ' !important;
}
</style>' . "\n";
    }

    /**
     * Cambia la URL del logo en el login para apuntar al home del subdominio
     *
     * @param string $url URL actual
     * @return string
     */
    public static function login_logo_url( $url ) {
        if ( self::$active_subdomain ) {
            return 'https://' . self::$active_subdomain . '.' . self::BASE_DOMAIN . '/';
        }
        return $url;
    }

    /**
     * Redirige al perfil del alumno tras el login desde un subdominio.
     * Los administradores siempre van al panel de administración.
     *
     * @param string  $redirect_to           URL de redirección solicitada
     * @param string  $requested_redirect_to URL original en el formulario
     * @param WP_User|WP_Error $user         Usuario que acaba de autenticarse
     * @return string URL de redirección final
     */
    public static function handle_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( ! self::$active_subdomain ) {
            return $redirect_to;
        }

        if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
            return $redirect_to;
        }

        // Administradores y editores van al wp-admin
        if ( user_can( $user, 'manage_options' ) || user_can( $user, 'edit_posts' ) ) {
            return admin_url();
        }

        // El resto de usuarios (alumnos, líderes de grupo) van a Mi Perfil
        return home_url( '/mi-perfil/' );
    }

    /**
     * Filtra la URL de login para usar el subdominio activo cuando corresponde
     *
     * @param string $login_url URL de login
     * @param string $redirect  URL de redirección
     * @param bool   $force_reauth Forzar reautenticación
     * @return string
     */
    public static function filter_login_url( $login_url, $redirect, $force_reauth ) {
        if ( ! self::$active_subdomain ) {
            return $login_url;
        }

        $subdomain_url = 'https://' . self::$active_subdomain . '.' . self::BASE_DOMAIN . '/wp-login.php';

        if ( $redirect ) {
            $subdomain_url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $subdomain_url );
        }
        if ( $force_reauth ) {
            $subdomain_url = add_query_arg( 'reauth', '1', $subdomain_url );
        }

        return $subdomain_url;
    }
}

add_action( 'plugins_loaded', array( 'LMSEU_Subdomain_Detector', 'init' ), 5 );
