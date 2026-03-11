---
paths:
  - "wordpress/wp-content/plugins/**"
---

# WordPress Plugin Development

Estas reglas aplican cuando se trabaja con archivos de plugins WordPress.

## Registro de Habilidades (Abilities)

Usar siempre el estándar de Abilities API:

```php
add_action( 'wp_abilities_api_init', function() {
    if ( ! function_exists( 'wp_register_ability' ) ) return;
    
    wp_register_ability( 'plugin-name/ability-name', [
        'label' => 'Human Readable Label',
        'description' => 'Description of what this ability does',
        'input_schema' => [
            'type' => 'object',
            'properties' => [
                'param_name' => [
                    'type' => 'string',
                    'description' => 'Parameter description'
                ]
            ]
        ],
        'output_schema' => [
            'type' => 'object',
            'properties' => [
                'result_field' => [
                    'type' => 'string'
                ]
            ]
        ],
        'execute_callback' => function( $input ) {
            // Lógica de ejecución
            return $result;
        },
        'permission_callback' => function() {
            return current_user_can( 'capability_required' );
        }
    ] );
}, 10 );
```

## Categorías de Habilidades

Registrar categorías organizadas en `learndash`, `support`, `wordpress`, etc.

## Manejo de Errores

Retornar `WP_Error` en lugar de lanzar excepciones:

```php
if ( empty( $user_id ) ) {
    return new WP_Error(
        'invalid_user',
        'User ID is required',
        ['status' => 400]
    );
}
```

## Hooks y Filtros

Usar hooks de WordPress para extender funcionalidad:

```php
// Actions
add_action( 'init', 'my_plugin_init' );
add_action( 'admin_enqueue_scripts', 'my_plugin_admin_scripts' );

// Filters
add_filter( 'plugin_action_links', 'my_plugin_add_settings_link', 10, 2 );
```

## Archivos de Debug

Para debugging temporal, crear archivos `debug_nombre.php` en el directorio raíz del plugin. **NUNCA incluir estos archivos en commits**.

## Referencias

- Ver: `wordpress/wp-content/plugins/lmseu-mcp-abilities/` para ejemplos de implementación
- Ver: `wordpress/wp-content/plugins/abilities-api/docs/` para documentación oficial