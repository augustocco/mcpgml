---
paths:
  - "wordpress/wp-content/themes/**"
---

# WordPress Theme Development

Estas reglas aplican cuando se trabaja con el tema personalizado eunolms.

## Estilo CSS

Usar BEM o convención de nombres con namespacing:

```css
/* ✅ CORRECTO - Namespacing específico */
.euno-student-profile {
    &__header {
        background: #f5f5f5;
    }
    
    &__progress-bar {
        width: 100%;
    }
}

/* ❌ INCORRECTO - Sin namespacing */
.header {
    /* Puede entrar en conflicto con otros plugins */
}
```

## Shortcodes

Usar prefijo único para todos los shortcodes:

```php
// ✅ CORRECTO
add_shortcode( 'euno_enrolled_courses', 'euno_render_enrolled_courses' );
add_shortcode( 'euno_student_profile', 'euno_render_student_profile' );
add_shortcode( 'euno_course_progress', 'euno_render_course_progress' );

// ❌ INCORRECTO
add_shortcode( 'courses', 'render_courses' );
add_shortcode( 'profile', 'render_profile' );
```

## Estructura de Archivos

El tema `eunolms` debe seguir esta estructura:
- `header.php` - Header del sitio
- `footer.php` - Footer del sitio
- `functions.php` - Funciones del tema
- `style.css` - Estilos principales
- `page-{slug}.php` - Plantillas de página específicas
- `sidebar.php` - Sidebar si aplica
- `css/` - Archivos CSS adicionales
- `js/` - Archivos JavaScript

## WordPress Hooks

Usar `wp_enqueue_scripts` para cargar assets:

```php
function euno_enqueue_scripts() {
    wp_enqueue_style(
        'euno-style',
        get_template_directory_uri() . '/style.css',
        [],
        '1.0.0'
    );
    
    wp_enqueue_script(
        'euno-main',
        get_template_directory_uri() . '/js/main.js',
        ['jquery'],
        '1.0.0',
        true
    );
}
add_action( 'wp_enqueue_scripts', 'euno_enqueue_scripts' );
```

## Referencias

- Ver: `wordpress/wp-content/themes/eunolms/` para ejemplos de implementación
- WordPress Theme Handbook: https://developer.wordpress.org/themes/