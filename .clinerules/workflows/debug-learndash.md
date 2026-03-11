# Debug de LearnDash

Workflow para diagnosticar y resolver problemas con LearnDash LMS. Este workflow guía el proceso de identificación y solución de problemas.

## Step 1: Verificar estado de LearnDash
<execute_command>
<command>cd wordpress && wp plugin list --name=sfwd-lms</command>
</execute_command>

Verifica que LearnDash esté activo y muestra su versión.

## Step 2: Verificar cursos existentes
<execute_command>
<command>cd wordpress && wp post list --post_type=sfwd-courses --fields=ID,post_title,post_status --posts_per_page=10</command>
</execute_command>

Muestra los últimos 10 cursos con su estado.

## Step 3: Verificar usuarios registrados
<execute_command>
<command>cd wordpress && wp user list --fields=ID,user_email,user_registered --number=10</command>
</execute_command>

Muestra los últimos 10 usuarios registrados.

## Step 4: Obtener información del problema
Pregunta al usuario:
- ¿Qué problema estás experimentando? (describe el issue)
- ¿Cuándo ocurrió por primera vez?
- ¿Afecta a todos los usuarios o solo a algunos?
- ¿Hay algún error message visible?

## Step 5: Verificar errores en logs
<execute_command>
<command>cd wordpress/wp-content/plugins/lmseu-mcp-abilities && ls -la logs/</command>
</execute_command>

Lee el archivo de logs más reciente si existe.

## Step 6: Verificar dependencias
<execute_command>
<command>cd wordpress && wp plugin list --status=active</command>
</execute_command>

Lista todos los plugins activos para identificar conflictos potenciales.

## Step 7: Probar funcionalidad básica
Pregunta al usuario qué funcionalidad específica está fallando y selecciona el test apropiado:

Opciones comunes:
- Progreso de estudiantes
- Inscripción a cursos
- Acceso a lecciones
- Quizzes
- Certificados

## Step 8: Ejecutar test específico
Basado en la selección del Step 7, crea o ejecuta un test script apropiado.

Ejemplo para progreso de estudiantes:

```php
<?php
// Test progreso de estudiantes
require_once( __DIR__ . '/../../../wp-load.php' );

$user_id = get_current_user_id();
$courses = ld_get_mycourses( $user_id );

echo "<h2>Test: Progreso de Estudiantes</h2>";
echo "<p>Usuario ID: $user_id</p>";
echo "<p>Cursos inscritos: " . count( $courses ) . "</p>";

foreach ( $courses as $course ) {
    $progress = learndash_user_get_course_progress( $user_id, $course->ID );
    $percentage = learndash_course_get_completed_percentage( $user_id, $course->ID );
    
    echo "<h3>Curso: " . $course->post_title . "</h3>";
    echo "<p>Completado: " . $progress['completed'] . "/" . $progress['total'] . " pasos</p>";
    echo "<p>Porcentaje: $percentage%</p>";
}
```

## Step 9: Analizar resultados
Muestra los resultados del test y:
- Identifica patrones o problemas comunes
- Sugiere posibles soluciones
- Recomienda acciones correctivas

## Step 10: Documentar y crear issue
Si se encontró un bug:
1. Documenta el problema
2. Crea un archivo de reporte en `wordpress/wp-content/plugins/lmseu-mcp-abilities/logs/`
3. Sugiere crear un issue en el repositorio

## Step 11: Verificar soluciones sugeridas
Si se encontró una solución:
1. Implementa la solución
2. Ejecuta el test nuevamente
3. Verifica que el problema esté resuelto