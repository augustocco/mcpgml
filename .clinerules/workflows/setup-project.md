# Setup de Proyecto

Workflow para configurar un entorno de desarrollo MCPGML desde cero o para nuevos desarrolladores. Incluye instalación de dependencias, configuración de base de datos y verificación del entorno.

## Step 1: Verificar requisitos del sistema
Pregunta al usuario su entorno y verifica:
- Sistema operativo (Windows/Mac/Linux)
- Versión de Node.js
- Versión de PHP
- Versión de WordPress
- Acceso a base de datos MySQL/MariaDB

<execute_command>
<command>node --version && php --version</command>
</execute_command>

## Step 2: Verificar Docker/Kubernetes (opcional)
Si el proyecto usa Docker o Kubernetes, verifica la instalación:

<execute_command>
<command>docker --version && kubectl --version</command>
</execute_command>

## Step 3: Clonar el repositorio
<execute_command>
<command>git clone https://github.com/augustocco/mcpgml.git</command>
</execute_command>

Si ya está clonado, verifica que estemos en el directorio correcto:

<execute_command>
<command>pwd && ls -la</command>
</execute_command>

## Step 4: Instalar dependencias de WordPress
<execute_command>
<command>cd wordpress && composer install</command>
</execute_command>

## Step 5: Configurar base de datos
Pregunta al usuario:
- Nombre de la base de datos
- Usuario de la base de datos
- Contraseña
- Host de la base de datos

Crea o actualiza `wordpress/wp-config.php` con la configuración proporcionada.

## Step 6: Instalar plugins activos
<execute_command>
<command>cd wordpress && wp plugin install sfwd-lms --activate</command>
</execute_command>

<execute_command>
<command>cd wordpress && wp plugin install abilities-api --activate</command>
</execute_command>

<execute_command>
<command>cd wordpress && wp plugin install mcp-adapter --activate</command>
</execute_command>

<execute_command>
<command>cd wordpress && wp plugin install lmseu-mcp-abilities --activate</command>
</execute_command>

## Step 7: Activar el tema
<execute_command>
<command>cd wordpress && wp theme activate eunolms</command>
</execute_command>

## Step 8: Configurar MCP Adapter
Verifica la configuración del MCP adapter en `c:/Users/augus/AppData/Roaming/Code/User/globalStorage/saoudrizwan.claude-dev/settings/cline_mcp_settings.json`:

<read_file>
<path>c:/Users/augus/AppData/Roaming/Code/User/globalStorage/saoudrizwan.claude-dev/settings/cline_mcp_settings.json</path>
</read_file>

Verifica que el servidor MCP de WordPress esté correctamente configurado.

## Step 9: Probar conexión MCP
Intenta conectar con el servidor MCP:

<use_mcp_tool>
<server_name>wordpress-eks12</server_name>
<tool_name>mcp-adapter-discover-abilities</tool_name>
<arguments>{}</arguments>
</use_mcp_tool>

## Step 10: Crear usuario de prueba (opcional)
Pregunta si desea crear un usuario de prueba para desarrollo:

<execute_command>
<command>cd wordpress && wp user create testuser testuser@example.com --role=administrator --user_pass=testpass123</command>
</execute_command>

## Step 11: Verificar configuración de Cline
Verifica que los archivos de configuración de Cline estén en su lugar:

<execute_command>
<command>ls -la .cline/ && ls -la .clinerules/</command>
</execute_command>

Muestra el resumen de la configuración:
- Reglas universales: 01-universal-standards.md
- Reglas condicionales: 02-wordpress-plugins.md, 03-theme-development.md
- Skills disponibles: learndash-integration, mcp-ability-development
- Workflows disponibles: create-mcp-ability, test-mcp-abilities, release-preparation, debug-learndash, setup-project

## Step 12: Resumen y próximos pasos
Muestra un resumen del setup completo:

✅ Entorno configurado
✅ WordPress instalado y configurado
✅ Plugins activos
✅ Tema activado
✅ MCP Adapter conectado
✅ Cline configurado

Próximos pasos sugeridos:
- Crear una nueva habilidad MCP: `/create-mcp-ability.md`
- Probar habilidades existentes: `/test-mcp-abilities.md`
- Revisar la documentación del proyecto
- Configurar permisos y roles de usuario