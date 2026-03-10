<?php
class LMSEU_MCP_HTTP_Auth {
    public static function init() {
        add_filter( 'mcp_adapter_default_server_config', array( __CLASS__, 'add_http_auth_callback' ) );
    }

    public static function add_http_auth_callback( $config ) {
        $config['transport_permission_callback'] = array( __CLASS__, 'check_http_basic_auth' );
        return $config;
    }

    public static function check_http_basic_auth( $request ) {
        if ( ! function_exists( 'get_current_user_id' ) ) {
            return false;
        }

        $user_id = get_current_user_id();

        if ( $user_id > 0 ) {
            return true;
        }

        $auth_header = $request->get_header( 'Authorization' );

        if ( ! $auth_header ) {
            return false;
        }

        if ( ! preg_match( '/Basic\s+(.*)$/i', $auth_header, $matches ) ) {
            return false;
        }

        $decoded = base64_decode( $matches[1] );

        if ( ! $decoded ) {
            return false;
        }

        list( $username, $password ) = explode( ':', $decoded, 2 );

        $user = wp_authenticate( $username, $password );

        if ( is_wp_error( $user ) ) {
            return false;
        }

        wp_set_current_user( $user->ID );

        return true;
    }
}

add_action( 'plugins_loaded', array( 'LMSEU_MCP_HTTP_Auth', 'init' ) );
