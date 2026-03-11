<?php
/**
 * Maneja la carga del template personalizado para lecciones.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_Custom_Lesson_Template {
    public static function init() {
        // Usar un hook que ocurre antes de template_include para asegurar que todo está listo
        add_filter( 'template_include', array( __CLASS__, 'load_custom_lesson_template' ), 99999 );
    }

    public static function load_custom_lesson_template( $template ) {
        // Si no estamos en una lección, tema o quiz, no hacer nada
        if ( ! is_singular( array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz' ) ) ) {
            return $template;
        }

        // Forzar nuestro template
        $custom_template = plugin_dir_path( __FILE__ ) . '../templates/custom-lesson.php';
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }

        return $template;
    }
}
LMSEU_Custom_Lesson_Template::init();