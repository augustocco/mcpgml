# Crear Nueva Habilidad MCP

Workflow para crear una nueva habilidad MCP siguiendo los estándares del proyecto MCPGML. Este workflow guía el proceso de crear, registrar y probar una nueva habilidad.

## Step 1: Solicitar información de la habilidad
Pregunta al usuario:
- Nombre de la habilidad (formato: `category/ability-name`)
- Descripción breve de lo que hace
- Categoría (learndash, support, wordpress)
- Parámetros de entrada requeridos

## Step 2: Crear el archivo de la habilidad
Crea un nuevo archivo PHP en `wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/` con el nombre apropiado (ej. `class-new-ability.php`).

Usa el siguiente template:

```php
<?php
/**
 * Descripción de la habilidad
 *
 * @package LMSEU_MCP_Abilities
 * @since 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class LMSEU_New_Ability {
    
    public function __construct() {
        add_action( 'wp_abilities_api_init', [ $this, 'register_ability' ], 10 );
    }
    
    /**
     * Registrar la habilidad
     */
    public function register_ability() {
        if ( ! function_exists( 'wp_register_ability' ) ) {
            return;
        }
        
        wp_register_ability( 'category/ability-name', [
            'label' => 'Human Readable Label',
            'description' => 'Description of what this ability does',
            'category' => 'category',
            
            'input_schema' => [
                'type' => 'object',
                'properties' => [],
                'required' => []
            ],
            
            'output_schema' => [
                'type' => 'object',
                'properties' => []
            ],
            
            'execute_callback' => [ $this, 'execute' ],
            'permission_callback' => [ $this, 'check_permissions' ]
        ] );
    }
    
    /**
     * Ejecutar la habilidad
     *
     * @param array $input Datos de entrada
     * @return array|WP_Error Resultado o error
     */
    public function execute( $input ) {
        // Sanitizar inputs
        // Implementar lógica
        // Retornar resultado
    }
    
    /**
     * Verificar permisos
     *
     * @return bool
     */
    public function check_permissions() {
        return current_user_can( 'manage_options' );
    }
}

new LMSEU_New_Ability();
```

## Step 3: Actualizar el archivo principal del plugin
Agrega el include del nuevo archivo en `wordpress/wp-content/plugins/lmseu-mcp-abilities/lmseu-mcp-abilities.php`:

```php
require_once plugin_dir_path( __FILE__ ) . 'includes/class-new-ability.php';
```

## Step 4: Crear archivo de prueba
Crea un archivo de prueba `test_new_ability.php` en el directorio raíz del plugin para facilitar el desarrollo:

```php
<?php
// Test script para new_ability
require_once( __DIR__ . '/../../../wp-load.php';

$input = [];
$result = wp_abilities_api_execute( 'category/ability-name', $input );

echo '<pre>';
print_r( $result );
echo '</pre>';
```

## Step 5: Verificar el registro
Lee el archivo `wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/class-ability-registrar.php` para verificar el patrón de registro actual.

Asegúrate de que la habilidad esté correctamente registrada según el estándar del proyecto.

## Step 6: Commit
Realiza un commit con el formato apropiado:

```
feat(lmseu): agregar habilidad [ability-name] para [descripción]
```

## Step 7: Limpiar archivos de prueba
Recuerda al usuario que los archivos de prueba (test_*.php) NO deben incluirse en el commit final.