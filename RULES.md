# REGLAS DEL PROYECTO MCPGML

## 📋 VISIÓN GENERAL

Este proyecto es una plataforma de aprendizaje (LMS) basada en WordPress que integra **Model Context Protocol (MCP)** para permitir interacción con agentes de IA. Combina:

- **WordPress + LearnDash**: Plataforma LMS principal
- **MCP Adapter**: Exposición de habilidades de WordPress como herramientas MCP
- **Abilities API Personalizadas**: Funcionalidades específicas para EUNO
- **Tema Personalizado**: eunolms

---

## 🏗️ ARQUITECTURA

### Estructura de Directorios
```
MCPGML/
├── wordpress/
│   ├── wp-content/
│   │   ├── plugins/
│   │   │   ├── mcp-adapter/           # Core MCP (oficial)
│   │   │   ├── abilities-api/        # API de Habilidades
│   │   │   ├── lmseu-mcp-abilities/ # ABILIDADES PERSONALIZADAS
│   │   │   └── sfwd-lms/            # LearnDash LMS
│   │   └── themes/
│   │       └── eunolms/            # Tema personalizado
```

### Componentes Principales

1. **MCP Adapter** (`wordpress/wp-content/plugins/mcp-adapter/`)
   - Convierte habilidades de WordPress en herramientas MCP
   - Proporciona transporte HTTP y STDIO
   - Maneja servidores, herramientas, recursos y prompts MCP

2. **Abilities API** (`wordpress/wp-content/plugins/abilities-api/`)
   - API estándar para registrar habilidades
   - Define esquemas de entrada/salida
   - Sistema de permisos

3. **LMSEU MCP Abilities** (`wordpress/wp-content/plugins/lmseu-mcp-abilities/`)
   - Habilidades personalizadas para EUNO
   - Integración con LearnDash
   - Gestión de perfiles, cursos e informes

4. **LearnDash** (`wordpress/wp-content/plugins/sfwd-lms/`)
   - LMS principal
   - Gestión de cursos, lecciones, quizzes
   - Progreso de estudiantes

---

## 💻 ESTÁNDARES DE CODIFICACIÓN

### PHP
- **Versión**: PHP 7.4+
- **Estándar**: [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- **Indentación**: 4 espacios (NO tabs)
- **Convenciones**:
  - Nombres de clases: `Class_Name`
  - Nombres de funciones/métodos: `function_name()`
  - Nombres de variables: `$variable_name`
  - Constantes: `CONSTANT_NAME`

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

### JavaScript (Frontend)
- **Estándar**: ES6+
- **Indentación**: 2 espacios o 4 espacios (consistente)
- **Convenciones**: camelCase para variables y funciones

```javascript
// ✅ CORRECTO
const studentProfile = {
    loadProfile: function(userId) {
        return fetch(`/api/student/${userId}`);
    }
};

// ❌ INCORRECTO
const Student_Profile={
    Load_Profile: function(User_ID){
        return fetch(`/api/student/${User_ID}`);
    }
}
```

### SQL
- **Convenciones**: Nombres de tablas en minúsculas con guiones bajos
- **Prefijo**: Siempre usar `$wpdb->prefix`
- **Índices**: Usar nombres descriptivos

```sql
-- ✅ CORRECTO
CREATE TABLE {$wpdb->prefix}euno_time_tracking (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    seconds int(11) DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY user_course_step (user_id, course_id, step_id)
) {$charset_collate};

-- ❌ INCORRECTO
CREATE TABLE EunoTimeTracking (
    ID bigint(20) NOT NULL AUTO_INCREMENT,
    UserID bigint(20) NOT NULL,
    PRIMARY KEY (ID)
)
```

---

## 📦 ESTRUCTURA DE PLUGINS

### Registro de Habilidades

Siempre usar el estándar de Abilities API:

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

### Categorías de Habilidades

Registrar categorías organizadas:

```php
add_action( 'wp_abilities_api_categories_init', function() {
    wp_register_ability_category( 'learndash', [
        'label' => 'LearnDash',
        'description' => 'Habilidades relacionadas con el LMS LearnDash.'
    ] );
    
    wp_register_ability_category( 'support', [
        'label' => 'Soporte',
        'description' => 'Habilidades de soporte técnico y utilidades.'
    ] );
}, 10 );
```

---

## 🔒 SEGURIDAD Y PERMISOS

### Validación de Permisos
```php
// ✅ CORRECTO - Validar en callback de permisos
'permission_callback' => function() {
    return current_user_can( 'manage_options' );
},

// ✅ CORRECTO - Validar dentro de execute_callback
'execute_callback' => function( $input ) {
    if ( ! current_user_can( 'required_capability' ) ) {
        return new WP_Error( 'forbidden', 'No tienes permisos' );
    }
    
    // Procesar request
}
```

### Sanitización de Datos
```php
// Sanitizar siempre inputs
$input['course_id'] = absint( $input['course_id'] );
$input['user_email'] = sanitize_email( $input['user_email'] );
$input['post_content'] = sanitize_text_field( $input['post_content'] );

// Escapar outputs
echo esc_html( $output );
echo esc_url( $url );
```

### Preparación de Queries SQL
```php
// ✅ CORRECTO - Usar $wpdb->prepare
$user_id = absint( $user_id );
$results = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}euno_time_tracking 
     WHERE user_id = %d",
    $user_id
) );

// ❌ INCORRECTO - Direct interpolation
$results = $wpdb->get_results( 
    "SELECT * FROM {$wpdb->prefix}euno_time_tracking 
     WHERE user_id = $user_id"
);
```

---

## 🗄️ BASE DE DATOS

### Creación de Tablas
```php
register_activation_hook( __FILE__, 'plugin_activate' );
function plugin_activate() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_table_name';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
```

### Actualización de Tablas
```php
// Usar dbDelta para cambios seguros
register_activation_hook( __FILE__, 'plugin_update' );
function plugin_update() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'plugin_table_name';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "ALTER TABLE $table_name 
        ADD COLUMN new_column varchar(255) AFTER existing_column;";
    
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
```

---

## 🧪 TESTING

### Archivos de Debug
- Crear archivos de debug en directorio raíz del plugin
- Nombrar: `debug_descriptivo.php`
- Usar prefijo `test_` para scripts de prueba
- NO incluir archivos de debug en commits al repo

```php
// debug_lesson_progress.php
<?php
// Solo para desarrollo - NO en producción
error_log( 'Debug: ' . print_r( $data, true ) );
?>
```

### Pruebas Unitarias
```php
// Ejemplo básico
public function test_get_user_progress() {
    $user_id = 1;
    $course_id = 100;
    
    $result = $this->manager->get_user_progress( $user_id, $course_id );
    
    $this->assertIsArray( $result );
    $this->assertArrayHasKey( 'percentage', $result );
}
```

---

## 📝 COMENTARIOS Y DOCUMENTACIÓN

### Comentarios de PHP
```php
/**
 * Breve descripción de la clase
 *
 * Descripción más detallada si es necesaria
 *
 * @package LMSEU_MCP_Abilities
 * @since 2.0.0
 */
class LMSEU_LearnDash_Manager {
    
    /**
     * Descripción del método
     *
     * @param int $user_id ID del usuario
     * @param int $course_id ID del curso
     * @return array|WP_Error Datos del progreso o error
     */
    public function get_user_progress( $user_id, $course_id ) {
        // Implementación
    }
}
```

### Documentación en Código
- Documentar todos los métodos públicos
- Incluir @param, @return, @throws cuando sea apropiado
- Usar @package para organización

---

## 🎨 ESTILOS Y FRONTEND

### CSS en Tema
```css
/* ✅ CORRECTO - Namespacing específico */
.euno-student-profile {
    &__header {
        /* estilos */
    }
    
    &__progress-bar {
        /* estilos */
    }
}

/* ❌ INCORRECTO - Sin namespacing */
.header {
    /* Puede entrar en conflicto con otros plugins */
}
```

### Shortcodes
```php
// ✅ CORRECTO - Prefijo único
add_shortcode( 'euno_enrolled_courses', 'euno_render_enrolled_courses' );
function euno_render_enrolled_courses( $atts ) {
    // Implementación
    return $output;
}

// ❌ INCORRECTO - Prefijo genérico
add_shortcode( 'courses', 'render_courses' );
```

---

## 🔄 GIT Y VERSIONADO

### Commits
```bash
# Formato de mensajes de commit
type(scope): descripción breve

# Tipos:
# - feat: nueva funcionalidad
# - fix: corrección de bug
# - docs: cambios en documentación
# - style: formato de código (sin lógica)
# - refactor: refactorización
# - test: agregar/actualizar tests
# - chore: tareas de mantenimiento

# Ejemplos:
feat(lmseu): agregar habilidad para obtener progreso de estudiantes
fix(learndash): corregir cálculo de porcentaje de cursos completados
docs(readme): actualizar instrucciones de instalación
```

### Versionado (Semantic Versioning)
- **MAJOR**: Cambios incompatibles hacia atrás
- **MINOR**: Nueva funcionalidad compatible hacia atrás
- **PATCH**: Corrección de bugs compatible hacia atrás

Ejemplo: `2.8.0` → `2.8.1` → `2.9.0` → `3.0.0`

---

## 🚀 DESPLIEGUE

### Ambiente de Desarrollo
- Usar `.gitignore` para excluir:
  - `node_modules/`
  - `*.log`
  - `debug_*.php`
  - `test_*.php`
  - `.env` si existe

### Ambiente de Producción
- Minificar CSS/JS antes de deploy
- Deshabilitar `WP_DEBUG` en producción
- Usar transientes de WordPress para caché cuando sea apropiado

---

## 📚 RECURSOS

### Documentación Oficial
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [LearnDash Documentation](https://www.learndash.com/support/docs/)
- [MCP Protocol Specification](https://modelcontextprotocol.io/)
- [WordPress Abilities API](https://github.com/WordPress/abilities-api)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)

### Herramientas
- **PHPStan**: Análisis estático de PHP
- **PHP_CodeSniffer**: Chequeo de coding standards
- **WordPress Lint**: Linter específico de WordPress

---

## ⚠️ ADVERTENCIAS IMPORTANTES

1. **NUNCA** hacer commits con archivos de debug
2. **SIEMPRE** validar y sanitizar datos de entrada
3. **NUNCA** hacer queries SQL sin `$wpdb->prepare()`
4. **SIEMPRE** usar prefijos únicos para funciones/clases
5. **NUNCA** exponer datos sensibles en logs o errores
6. **SIEMPRE** verificar permisos del usuario antes de ejecutar acciones
7. **NUNCA** confiar en datos de `$_GET`, `$_POST`, `$_REQUEST` sin sanitizar
8. **SIEMPRE** usar transacciones de WordPress para operaciones complejas de BD

---

## ✅ CHECKLIST ANTES DE COMMIT

- [ ] Código sigue WordPress Coding Standards
- [ ] Todos los inputs están validados y sanitizados
- [ ] Todos los queries SQL usan `$wpdb->prepare()`
- [ ] No hay archivos de debug incluidos
- [ ] Funciones/clases tienen prefijos únicos
- [ ] Permisos están correctamente validados
- [ ] Comentario del commit sigue el formato establecido
- [ ] Versión del plugin actualizada (si aplica)
- [ ] Documentación actualizada (si aplica)
- [ ] Código probado en ambiente de desarrollo

---

## 📞 SOPORTE

Para preguntas o problemas:
1. Revisar la documentación oficial de WordPress
2. Consultar los recursos listados arriba
3. Buscar en el código existente patrones similares
4. Preguntar al equipo de desarrollo antes de implementar cambios mayores

---

**Última actualización**: Marzo 2026  
**Versión**: 1.0.0