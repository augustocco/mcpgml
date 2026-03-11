# Reglas y Workflows de MCPGML

Este directorio contiene todas las reglas, estándares y workflows que deben seguirse al trabajar en el proyecto MCPGML.

## 📋 Reglas Principales

### [01-universal-standards.md](./01-universal-standards.md)
Estándares universales que aplican a todo el proyecto, sin importar el archivo o contexto actual.
- PHP Coding Standards (PHP 7.4+)
- JavaScript (Frontend ES6+)
- Seguridad Obligatoria (validación y sanitización)
- Prefijos Únicos para evitar conflictos
- Validación de Permisos
- Comentarios PHPDoc
- Convención de Commits

### [02-wordpress-plugins.md](./02-wordpress-plugins.md)
Reglas específicas para el desarrollo de plugins de WordPress.
- Estructura de plugins
- Hooks y filtros de WordPress
- API REST de WordPress
- Capacidades y roles de usuarios
- Integración con LearnDash

### [03-theme-development.md](./03-theme-development.md)
Reglas para el desarrollo del tema personalizado `eunolms`.
- Estilo CSS (BEM o namespacing)
- Shortcodes con prefijo único
- Estructura de archivos del tema
- WordPress Hooks para cargar assets

### [04-mcp-image-analysis.md](./04-mcp-image-analysis.md)
**IMPORTANTE:** Regla obligatoria para análisis de imágenes.
- **SIEMPRE** usar el servidor MCP `zai-mcp-server`
- Herramientas especializadas para diferentes tipos de imágenes
- Procedimiento paso a paso para análisis de imágenes

## 🔄 Workflows

### [workflows/new-task-workflow.md](./workflows/new-task-workflow.md) ⭐ **MÁS IMPORTANTE**
Flujo de trabajo estándar para CUALQUIER nueva tarea. **DEBE seguirse siempre.**

**Pasos obligatorios:**
1. ✅ Exploración inicial del códigobase
2. ✅ Planificación y aprobación del enfoque
3. ✅ Implementación de la solución
4. ✅ Pruebas de funcionalidad
5. ✅ Commit con mensaje descriptivo
6. ✅ Despliegue a producción
7. ✅ Limpieza post-despliegue

### [workflows/create-mcp-ability.md](./workflows/create-mcp-ability.md)
Workflow para crear una nueva habilidad MCP siguiendo los estándares del proyecto.

### [workflows/test-mcp-abilities.md](./workflows/test-mcp-abilities.md)
Workflow para probar habilidades MCP existentes.

### [workflows/release-preparation.md](./workflows/release-preparation.md)
Workflow para preparar releases y versiones importantes.

### [workflows/debug-learndash.md](./workflows/debug-learndash.md)
Workflow para depurar problemas relacionados con LearnDash.

### [workflows/setup-project.md](./workflows/setup-project.md)
Workflow para configurar el proyecto desde cero.

## 🚀 Comienzo Rápido

### Para el Asistente de IA (Cline)

**AL INICIAR CUALQUIER NUEVA TAREA:**

1. **Leer este README** primero para entender el contexto
2. **Seguir el workflow**: `.clinerules/workflows/new-task-workflow.md`
3. **Incluir task_progress** con el checklist del workflow
4. **Explorar el códigobase** antes de implementar
5. **Seguir los estándares** de los archivos de reglas principales
6. **No olvidar el despliegue** al finalizar

### Checklist Inicial Automático

```markdown
- [ ] 1. Exploración inicial del códigobase
- [ ] 2. Planificación y aprobación del enfoque
- [ ] 3. Implementación de la solución
- [ ] 4. Pruebas de funcionalidad
- [ ] 5. Commit con mensaje descriptivo
- [ ] 6. Despliegue a producción
- [ ] 7. Limpieza post-despliegue
```

## 📦 Comandos de Despliegue Típicos

### Git
```bash
git status
git add .
git commit -m "tipo(alcance): descripción breve"
git push origin main
```

### Kubernetes
```bash
kubectl config current-context
kubectl apply -f k8s/deployment.yaml
kubectl rollout status deployment/mcpgml-app
kubectl get pods
kubectl logs -f deployment/mcpgml-app
```

### WordPress
```bash
# Activar/desactivar plugins
wp plugin activate lmseu-mcp-abilities

# Limpiar cache
wp cache flush

# Actualizar base de datos
wp core update-db
```

## ⚠️ Reglas de Oro

1. **NUNCA** saltar la exploración inicial del códigobase
2. **SIEMPRE** validar y sanitizar inputs en PHP
3. **SIEMPRE** usar `task_progress` para rastrear progreso
4. **SIEMPRE** seguir el formato de commits estándar
5. **NUNCA** incluir archivos `debug_*.php` o `test_*.php` en commits
6. **NUNCA** hacer queries SQL sin preparar
7. **SIEMPRE** usar zai-mcp-server para analizar imágenes
8. **SIEMPRE** verificar el despliegue antes de completar

## 🔗 Referencias Externas

- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [LearnDash Documentation](https://www.learndash.com/support/docs/)
- [MCP Protocol Specification](https://modelcontextprotocol.io/)
- [WordPress Abilities API](https://github.com/WordPress/abilities-api)

## 📝 Notas para el Usuario

Este directorio `.clinerules/` se carga automáticamente al inicio de cada tarea. Todo el contenido aquí está disponible para el asistente de IA como contexto inicial.

Para agregar nuevas reglas o workflows:
1. Crear el archivo en el directorio apropiado
2. Seguir el formato Markdown existente
3. Actualizar este README con la nueva referencia
4. Mantener los estándares de claridad y detalle

---

**Última actualización:** 2026-03-10
**Versión:** 1.0.0