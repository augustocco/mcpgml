<?php
/**
 * Gestión de registros DNS en AWS Route53
 * Implementa AWS Signature V4 sin dependencias externas
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Route53_Manager {

    const SERVICE   = 'route53';
    const HOST      = 'route53.amazonaws.com';
    const ENDPOINT  = 'https://route53.amazonaws.com';

    /**
     * Registra o actualiza un subdominio CNAME en Route53
     *
     * @param string $subdomain   Subdominio a registrar (ej: "bancolombia")
     * @param string $domain      Dominio base (ej: "lmseunoconsulting.com")
     * @return true|WP_Error
     */
    public static function register_subdomain( $subdomain, $domain = 'lmseunoconsulting.com' ) {
        $access_key  = get_option( 'euno_aws_access_key', '' );
        $secret_key  = get_option( 'euno_aws_secret_key', '' );
        $region      = get_option( 'euno_aws_region', 'us-east-1' );
        $zone_id     = get_option( 'euno_aws_route53_zone_id', '' );
        $alb_host    = get_option( 'euno_aws_alb_hostname', '' );

        if ( empty( $access_key ) || empty( $secret_key ) || empty( $zone_id ) || empty( $alb_host ) ) {
            return new WP_Error( 'euno_aws_config', __( 'Credenciales AWS incompletas. Configura Settings → EUNO AWS.', 'lmseu-mcp-abilities' ) );
        }

        $fqdn    = $subdomain . '.' . $domain . '.';
        $body    = self::build_change_batch( $fqdn, $alb_host );
        $path    = '/2013-04-01/hostedzone/' . $zone_id . '/rrset';
        $result  = self::signed_request( 'POST', $path, $body, $access_key, $secret_key, $region );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $code = wp_remote_retrieve_response_code( $result );
        if ( $code !== 200 && $code !== 201 ) {
            $body_resp = wp_remote_retrieve_body( $result );
            return new WP_Error( 'euno_route53_error', sprintf( 'Route53 respondió HTTP %d: %s', $code, $body_resp ) );
        }

        return true;
    }

    /**
     * Construye el XML de ChangeBatch para upsert CNAME
     *
     * @param string $fqdn     Nombre DNS completo con punto final (ej: "bancolombia.lmseunoconsulting.com.")
     * @param string $alb_host Hostname del ALB
     * @return string XML
     */
    private static function build_change_batch( $fqdn, $alb_host ) {
        return '<?xml version="1.0" encoding="UTF-8"?>
<ChangeResourceRecordSetsRequest xmlns="https://route53.amazonaws.com/doc/2013-04-01/">
  <ChangeBatch>
    <Comment>Subdominio EUNO LMS registrado automáticamente</Comment>
    <Changes>
      <Change>
        <Action>UPSERT</Action>
        <ResourceRecordSet>
          <Name>' . esc_xml( $fqdn ) . '</Name>
          <Type>CNAME</Type>
          <TTL>300</TTL>
          <ResourceRecords>
            <ResourceRecord>
              <Value>' . esc_xml( $alb_host ) . '</Value>
            </ResourceRecord>
          </ResourceRecords>
        </ResourceRecordSet>
      </Change>
    </Changes>
  </ChangeBatch>
</ChangeResourceRecordSetsRequest>';
    }

    /**
     * Realiza una petición HTTP firmada con AWS Signature V4
     *
     * @param string $method     HTTP method (POST, GET, etc.)
     * @param string $path       Path del endpoint (ej: /2013-04-01/hostedzone/XXX/rrset)
     * @param string $body       Cuerpo de la petición
     * @param string $access_key AWS Access Key ID
     * @param string $secret_key AWS Secret Access Key
     * @param string $region     Región AWS
     * @return array|WP_Error Respuesta de wp_remote_post
     */
    private static function signed_request( $method, $path, $body, $access_key, $secret_key, $region ) {
        $now         = new DateTime( 'now', new DateTimeZone( 'UTC' ) );
        $date_stamp  = $now->format( 'Ymd' );
        $amz_date    = $now->format( 'Ymd\THis\Z' );

        $payload_hash = hash( 'sha256', $body );

        // Canonical headers (ordenados alfabéticamente)
        $canonical_headers = implode( "\n", array(
            'content-type:application/xml',
            'host:' . self::HOST,
            'x-amz-date:' . $amz_date,
        ) ) . "\n";

        $signed_headers = 'content-type;host;x-amz-date';

        $canonical_request = implode( "\n", array(
            $method,
            $path,
            '', // query string vacío
            $canonical_headers,
            $signed_headers,
            $payload_hash,
        ) );

        // String to sign
        $credential_scope = $date_stamp . '/' . $region . '/' . self::SERVICE . '/aws4_request';
        $string_to_sign   = implode( "\n", array(
            'AWS4-HMAC-SHA256',
            $amz_date,
            $credential_scope,
            hash( 'sha256', $canonical_request ),
        ) );

        // Signing key derivation
        $signing_key = self::derive_signing_key( $secret_key, $date_stamp, $region, self::SERVICE );

        // Firma final
        $signature = hash_hmac( 'sha256', $string_to_sign, $signing_key );

        $authorization = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $access_key,
            $credential_scope,
            $signed_headers,
            $signature
        );

        return wp_remote_post(
            self::ENDPOINT . $path,
            array(
                'timeout' => 15,
                'headers' => array(
                    'Authorization' => $authorization,
                    'Content-Type'  => 'application/xml',
                    'Host'          => self::HOST,
                    'x-amz-date'    => $amz_date,
                ),
                'body' => $body,
            )
        );
    }

    /**
     * Deriva la clave de firma AWS4 mediante HMAC encadenado
     *
     * @param string $secret  AWS Secret Access Key
     * @param string $date    Fecha formato Ymd
     * @param string $region  Región AWS
     * @param string $service Servicio AWS
     * @return string Clave binaria
     */
    private static function derive_signing_key( $secret, $date, $region, $service ) {
        $k_date    = hash_hmac( 'sha256', $date,              'AWS4' . $secret, true );
        $k_region  = hash_hmac( 'sha256', $region,            $k_date,          true );
        $k_service = hash_hmac( 'sha256', $service,           $k_region,        true );
        $k_signing = hash_hmac( 'sha256', 'aws4_request',     $k_service,       true );
        return $k_signing;
    }
}
