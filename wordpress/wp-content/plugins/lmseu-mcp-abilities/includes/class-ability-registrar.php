<?php
/**
 * Registrador central de Abilities MCP para LMSEU
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class LMSEU_MCP_Ability_Registrar {

    public static function register_abilities() {
        if ( ! function_exists( 'wp_register_ability' ) ) return;

        // Registrar Categorías con visibilidad REST y MCP
        wp_register_ability_category( 'support', array(
            'label' => 'Soporte',
            'meta'  => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true )
            ),
        ) );

        wp_register_ability_category( 'learndash', array(
            'label' => 'LearnDash',
            'meta'  => array(
                'show_in_rest' => true,
                'mcp'          => array( 'public' => true )
            ),
        ) );

        // Cargar y registrar habilidades de soporte
        $support_file = LMSEU_MCP_ABILITIES_PATH . 'includes/class-support-abilities.php';
        if ( file_exists( $support_file ) ) {
            require_once $support_file;
            if ( class_exists( 'LMSEU_Support_Abilities' ) ) {
                LMSEU_Support_Abilities::register();
            }
        }

        // Cargar y registrar habilidades de LearnDash
        $learndash_file = LMSEU_MCP_ABILITIES_PATH . 'includes/class-learndash-abilities.php';
        if ( file_exists( $learndash_file ) ) {
            require_once $learndash_file;
            if ( class_exists( 'LMSEU_LearnDash_Abilities' ) ) {
                LMSEU_LearnDash_Abilities::register();
            }
        }
    }
}
