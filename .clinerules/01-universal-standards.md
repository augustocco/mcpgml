---
# Reglas universales - siempre activas
---

# Estándares Universales MCPGML

Estas reglas aplican a todo el proyecto, sin importar el archivo o contexto actual.

## PHP Coding Standards

- **Versión**: PHP 7.4+
- **Indentación**: 4 espacios (NO tabs)
- **Convenciones de nombres**:
  - Clases: `Class_Name` (PascalCase con guiones bajos)
  - Funciones/Métodos: `function_name()` (snake_case)
  - Variables: `$variable_name` (snake_case)
  - Constantes: `CONSTANT_NAME` (UPPER_SNAKE_CASE)

```php
// ✅ CORRECTO
class LMSEU_LearnDash_Abilities {
    public function register() {
        $user_id = get_current_user_id();
    }
}

// ❌ INCORRECTO
class lmseu_learndash_abilities{
    public function Register(){
        $UserID=get_current_user_id();
    }
}
```

## JavaScript (Frontend)

- **Estándar**: ES6+
- **Indentación**: 2 o 4 espacios (consistente en todo el archivo)
- **Convenciones**: camelCase para variables y funciones

```javascript
// ✅ CORRECTO
const studentProfile = {
    loadProfile: function(userId) {
        return fetch(`/api/student/${userId}`);
    }
};
```

## Seguridad Obligatoria

**SIEMPRE** validar y sanitizar datos de entrada:

```php
$input['course_id'] = absint( $input['course_id'] );
$input['user_email'] = sanitize_email( $input['user_email'] );
$input['post_content'] = sanitize_text_field( $input['post_content'] );
```

**NUNCA** hacer queries SQL sin preparar:

```php
// ✅ CORRECTO
$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}euno_time_tracking WHERE user_id = %d",
    $user_id
) );

// ❌ INCORRECTO
$results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}euno_time_tracking WHERE user_id = $user_id" );
```

## Prefijos Únicos

SIEMPRE usar prefijos únicos para evitar conflictos:
- Funciones: `euno_` o `lmseu_`
- Clases: `EUNO_` o `LMSEU_`
- Shortcodes: `euno_` o `lmseu_`
- Tablas de BD: `{$wpdb->prefix}euno_`

## Validación de Permisos

SIEMPRE verificar permisos del usuario antes de ejecutar acciones:

```php
'permission_callback' => function() {
    return current_user_can( 'manage_options' );
}
```

## Comentarios PHPDoc

Documentar todos los métodos públicos con:

```php
/**
 * Descripción breve del método
 *
 * Descripción más detallada si es necesaria
 *
 * @param int $user_id ID del usuario
 * @param int $course_id ID del curso
 * @return array|WP_Error Datos del resultado o error
 */
```

## Prohibido en Commits

NUNCA incluir archivos de:
- `debug_*.php` - Archivos de debugging
- `test_*.php` - Scripts de prueba temporales
- Archivos con `TODO` o `FIXME` sin resolver

## Convención de Commits

Formato: `type(scope): descripción breve`

Tipos:
- `feat`: nueva funcionalidad
- `fix`: corrección de bug
- `docs`: cambios en documentación
- `style`: formato de código (sin lógica)
- `refactor`: refactorización
- `test`: agregar/actualizar tests
- `chore`: tareas de mantenimiento

Ejemplos:
```
feat(lmseu): agregar habilidad para obtener progreso de estudiantes
fix(learndash): corregir cálculo de porcentaje de cursos completados
docs(readme): actualizar instrucciones de instalación
```

## Recursos de Referencia

- WordPress Coding Standards: https://developer.wordpress.org/coding-standards/wordpress-coding-standards/
- LearnDash Documentation: https://www.learndash.com/support/docs/
- MCP Protocol Specification: https://modelcontextprotocol.io/
- WordPress Abilities API: https://github.com/WordPress/abilities-api