 cambio y a# Análisis de Imágenes con MCP zai

Esta regla aplica a todas las tareas que involucren revisión o análisis de imágenes.

## Regla Obligatoria

**SIEMPRE** utilizar el servidor MCP `zai-mcp-server` para revisar imágenes.

## Disponibilidad del Servidor

El servidor `zai-mcp-server` debe estar disponible con el comando:
```bash
npx -y @z_ai/mcp-server
```

## Herramientas Disponibles

El servidor zai-mcp-server proporciona las siguientes herramientas especializadas:

### 1. `ui_to_artifact`
Convierte capturas de UI en:
- Código frontend (`output_type='code'`)
- Prompts para IA (`output_type='prompt'`)
- Especificaciones de diseño (`output_type='spec'`)
- Descripciones en lenguaje natural (`output_type='description'`)

**Usar cuando:** El usuario quiere generar código o prompts desde un diseño de UI.

### 2. `extract_text_from_screenshot`
Extrae texto usando OCR avanzado de capturas de pantalla.

**Usar cuando:** La imagen contiene texto, código, terminal, documentación que necesita ser extraído.

### 3. `diagnose_error_screenshot`
Diagnostica y analiza mensajes de error, stack traces y excepciones.

**Usar cuando:** La imagen muestra un mensaje de error y el usuario necesita ayuda para entenderlo o solucionarlo.

### 4. `understand_technical_diagram`
Analiza diagramas técnicos: arquitectura, flujos, UML, ER, sequence, etc.

**Usar cuando:** La imagen es un diagrama técnico que necesita interpretación.

### 5. `analyze_data_visualization`
Analiza visualizaciones de datos, gráficos y dashboards.

**Usar cuando:** La imagen muestra datos visuales, métricas, tendencias, o dashboards.

### 6. `ui_diff_check`
Compara dos capturas de UI para identificar diferencias visuales.

**Usar cuando:** El usuario quiere comparar una UI esperada con la implementación actual.

### 7. `analyze_image`
Análisis general de imágenes para casos no cubiertos por las herramientas especializadas.

**Usar como FALLBACK** cuando ninguna de las herramientas anteriores encaja con la necesidad.

## Procedimiento

1. **Identificar el tipo de imagen**: Determinar qué tipo de contenido tiene la imagen (UI, texto, error, diagrama, datos, etc.)

2. **Seleccionar la herramienta apropiada**: Usar la herramienta especializada que mejor se adapte al tipo de imagen

3. **Invocar la herramienta**: Usar `use_mcp_tool` con:
   - `server_name`: `zai-mcp-server`
   - `tool_name`: La herramienta seleccionada
   - `arguments`: Los parámetros requeridos según la herramienta

## Ejemplo de Uso

```xml
<use_mcp_tool>
<server_name>zai-mcp-server</server_name>
<tool_name>ui_to_artifact</tool_name>
<arguments>
{
  "image_source": "imagenes/perfil.png",
  "output_type": "code",
  "prompt": "Generar código HTML/CSS para este diseño de perfil de usuario"
}
</arguments>
</use_mcp_tool>
```

## Importante

- **NUNCA** usar otras herramientas o métodos para analizar imágenes
- **NUNCA** omitir el uso de zai-mcp-server cuando se necesite revisar una imagen
- **SIEMPRE** usar la herramienta más especializada disponible para el tipo de imagen

## Referencias

- Documentación MCP: https://modelcontextprotocol.io/
- zai-mcp-server: `npx -y @z_ai/mcp-server`