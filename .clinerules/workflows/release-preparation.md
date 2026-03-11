# Preparación de Release

Workflow para preparar un nuevo release del proyecto MCPGML. Incluye verificación de cambios, pruebas, actualización de versiones y generación de changelog.

## Step 1: Verificar working directory limpio
<execute_command>
<command>git status --porcelain</command>
</execute_command>

Si hay cambios sin commit, pregunta al usuario si desea:
- Stash los cambios
- Commit los cambios primero
- Abortar el workflow

## Step 2: Obtener rama actual
<execute_command>
<command>git branch --show-current</command>
</execute_command>

Verificar que estemos en la rama correcta (main o develop).

## Step 3: Pull latest changes
<execute_command>
<command>git pull origin $(git branch --show-current)</command>
</execute_command>

## Step 4: Buscar archivos de prueba
<execute_command>
<command>find wordpress/wp-content/plugins/lmseu-mcp-abilities -name "test_*.php" -o -name "debug_*.php" -o -name "fix_*.php"</command>
</execute_command>

Si existen archivos de prueba, muestra la lista y pregunta si deben ser eliminados o movidos.

## Step 5: Actualizar version
Pregunta al usuario qué tipo de versión:
- Patch (x.x.X) - correcciones de bugs
- Minor (x.X.0) - nuevas funcionalidades backward compatible
- Major (X.0.0) - cambios breaking

Actualiza los archivos de versión según corresponda:
- `wordpress/wp-content/plugins/lmseu-mcp-abilities/lmseu-mcp-abilities.php` - versión del plugin
- `wordpress/wp-content/plugins/abilities-api/abilities-api.php` - versión del abilities API
- `wordpress/wp-content/themes/eunolms/style.css` - versión del tema

## Step 6: Generar changelog
<execute_command>
<command>git log --oneline $(git describe --tags --abbrev=0)..HEAD</command>
</execute_command>

Analiza los commits y genera un changelog estructurado con secciones:
- Added
- Changed
- Fixed
- Removed

Pregunta al usuario si desea editar el changelog generado.

## Step 7: Crear tag
<execute_command>
<command>git tag -a v{version} -m "Release v{version}"</command>
</execute_command>

Donde {version} es la versión especificada en el Step 5.

## Step 8: Push changes
<execute_command>
<command>git push origin $(git branch --show-current) --tags</command>
</execute_command>

## Step 9: Verificar documentación
Verifica que los archivos de documentación estén actualizados:
- README.md del proyecto
- .clinerules/ si hubo cambios en estándares
- .cline/skills/ si se agregaron nuevas habilidades

## Step 10: Resumen
Muestra un resumen del release:
- Versión
- Changelog resumido
- Archivos modificados
- Tag creado
- URL del release (si usa GitHub Releases)