# Testear Habilidades MCP

Workflow para probar habilidades MCP existentes o nuevas. Este workflow ayuda a verificar que las habilidades funcionan correctamente.

## Step 1: Listar habilidades disponibles
<execute_command>
<command>node mcp-stdio-wrapper.bat</command>
<requires_approval>false</requires_approval>
</execute_command>

O usa el MCP adapter para listar habilidades:

<use_mcp_tool>
<server_name>wordpress-eks12</server_name>
<tool_name>mcp-adapter-discover-abilities</tool_name>
<arguments>{}</arguments>
</use_mcp_tool>

## Step 2: Seleccionar habilidad a probar
Pregunta al usuario qué habilidad desea probar. Muestra la lista de habilidades disponibles del paso anterior.

## Step 3: Obtener información de la habilidad
Obtén detalles de la habilidad seleccionada:

<use_mcp_tool>
<server_name>wordpress-eks12</server_name>
<tool_name>mcp-adapter-get-ability-info</tool_name>
<arguments>{"ability_name": "selected_ability_name"}</arguments>
</use_mcp_tool>

## Step 4: Preparar parámetros de prueba
Basándote en el input_schema de la habilidad, pregunta al usuario qué parámetros usar para la prueba.

Sugiere valores de prueba comunes:
- Para user_id: 1 (admin) o 0 (usuario actual)
- Para course_id: IDs de cursos existentes
- Para post_id: IDs de posts existentes

## Step 5: Ejecutar la habilidad
Ejecuta la habilidad con los parámetros proporcionados:

<use_mcp_tool>
<server_name>wordpress-eks12</server_name>
<tool_name>mcp-adapter-execute-ability</tool_name>
<parameters>{"ability_name": "selected_ability", "parameters": {"param1": "value1", "param2": "value2"}}</parameters>
</use_mcp_tool>

## Step 6: Analizar resultados
Muestra el resultado y verifica:
- Si hay errores, muestra detalles del error
- Si es exitoso, verifica que el output_schema coincida
- Muestra el tiempo de ejecución si está disponible

## Step 7: Documentar resultados
Pregunta al usuario si desea:
- Guardar los resultados en un archivo
- Crear un caso de prueba
- Probar con diferentes parámetros
- Reportar un issue si encontró problemas

## Step 8: Opciones adicionales
Ofrece opciones adicionales:
- Prueba de estrés (múltiples llamadas)
- Prueba con diferentes permisos de usuario
- Comparación de resultados con versiones anteriores