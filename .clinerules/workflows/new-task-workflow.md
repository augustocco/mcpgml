# Flujo de Trabajo Estándar para Nuevas Tareas

Este workflow define el proceso estándar que debe seguirse para cualquier nueva tarea en el proyecto MCPGML, desde el inicio hasta el despliegue.

## Regla Obligatoria

**SIEMPRE** seguir este flujo de trabajo para cada nueva tarea. No omitir pasos.

## Flujo de Trabajo

### 1. Exploración Inicial

**Objetivo:** Entender el contexto y el códigobase antes de implementar.

```bash
# Listar estructura del proyecto
list_files(path=".", recursive=true)

# Buscar archivos relevantes
search_files(path=".", regex="palabra_clave", file_pattern="*.php")

# Leer archivos necesarios
read_file(path="ruta/al/archivo")
```

**Resultado:** Entender la arquitectura, localizar archivos relevantes y identificar patrones existentes.

---

### 2. Planificación

**Objetivo:** Definir objetivos, enfoque y archivos afectados.

Crear un checklist con `task_progress`:

```
- [ ] Análisis de requerimientos
- [ ] Identificación de archivos a modificar
- [ ] Implementación principal
- [ ] Pruebas
- [ ] Despliegue
```

**Resultado:** Plan claro y aprobado por el usuario.

---

### 3. Implementación

**Objetivo:** Desarrollar la solución siguiendo los estándares del proyecto.

**Consideraciones:**
- Seguir `.clinerules/01-universal-standards.md` para coding standards
- Validar y sanitizar inputs SIEMPRE
- Usar `replace_in_file` para cambios pequeños
- Usar `write_to_file` para archivos nuevos o cambios grandes
- Documentar con PHPDoc

**Resultado:** Código implementado siguiendo los estándares.

---

### 4. Pruebas

**Objetivo:** Verificar que la implementación funciona correctamente.

**Acciones:**
- Ejecutar scripts de prueba si existen
- Verificar funcionalidad en el entorno de desarrollo
- Revisar logs si hay errores
- Usar `execute_command` para ejecutar tests

**Resultado:** Funcionalidad verificada y sin errores.

---

### 5. Commit

**Objetivo:** Guardar cambios con un mensaje descriptivo.

**Formato de Commit:**
```
tipo(alcance): descripción breve

Descripción más detallada (opcional)

- Cambio específico 1
- Cambio específico 2
```

**Tipos:**
- `feat`: nueva funcionalidad
- `fix`: corrección de bug
- `docs`: cambios en documentación
- `style`: formato de código (sin lógica)
- `refactor`: refactorización
- `test`: agregar/actualizar tests
- `chore`: tareas de mantenimiento

**Ejemplos:**
```
feat(lmseu): agregar habilidad para obtener progreso de estudiantes

Añade nueva habilidad que permite obtener el progreso de un estudiante
en todos sus cursos inscritos usando la API de LearnDash.

- Implementa class-student-progress.php
- Registra habilidad student/get-progress
- Agrega pruebas unitarias
```

**Prohibido en Commits:**
- Archivos `debug_*.php`
- Archivos `test_*.php`
- Archivos con `TODO` o `FIXME` sin resolver

**Resultado:** Cambios guardados en Git.

---

### 6. Despliegue

**Objetivo:** Mover los cambios al entorno de producción.

**Proceso de Despliegue:**

#### 6.1. Verificar Entorno de Producción

```bash
# Verificar estado del repositorio
git status

# Verificar última versión en producción
git log origin/main -5
```

#### 6.2. Preparar Release (si aplica)

Si es una release importante, seguir el workflow en `.clinerules/workflows/release-preparation.md`

#### 6.3. Realizar Merge

```bash
# Asegurarse de estar en rama correcta
git checkout main

# Pull últimos cambios
git pull origin main

# Hacer merge de la rama de feature (si aplica)
git merge feature/tu-feature

# O simplemente empujar si está en main
git push origin main
```

#### 6.4. Desplegar a Kubernetes (si aplica)

```bash
# Verificar contexto de Kubernetes
kubectl config current-context

# Actualizar deployment (ajustar según tu configuración)
kubectl apply -f k8s/deployment.yaml
kubectl apply -f k8s/service.yaml

# Verificar status del deployment
kubectl rollout status deployment/mcpgml-app

# Ver logs del pod si hay problemas
kubectl logs -f deployment/mcpgml-app
```

#### 6.5. Verificar Despliegue

```bash
# Verificar que los pods están corriendo
kubectl get pods

# Verificar servicios
kubectl get services

# Probar endpoints (ajustar URL)
curl https://tu-dominio.com/health
```

**Resultado:** Cambios desplegados y verificados en producción.

---

### 7. Limpieza Post-Despliegue

**Objetivo:** Limpiar recursos temporales y documentar.

**Acciones:**
- Eliminar archivos de prueba temporales
- Limpiar logs si es necesario
- Actualizar documentación si hubo cambios
- Cerrar issues/tickets relacionados

**Resultado:** Entorno limpio y documentado.

---

## Checklist Automático

Al inicio de CADA tarea, incluir este `task_progress`:

```
- [ ] 1. Exploración inicial del códigobase
- [ ] 2. Planificación y aprobación del enfoque
- [ ] 3. Implementación de la solución
- [ ] 4. Pruebas de funcionalidad
- [ ] 5. Commit con mensaje descriptivo
- [ ] 6. Despliegue a producción
- [ ] 7. Limpieza post-despliegue
```

## Referencias

- Estándares Universales: `.clinerules/01-universal-standards.md`
- WordPress Plugins: `.clinerules/02-wordpress-plugins.md`
- Theme Development: `.clinerules/03-theme-development.md`
- MCP Image Analysis: `.clinerules/04-mcp-image-analysis.md`
- Release Preparation: `.clinerules/workflows/release-preparation.md`

## Notas Importantes

1. **NUNCA** saltarse el paso de exploración inicial
2. **SIEMPRE** validar y sanitizar inputs en PHP
3. **SIEMPRE** incluir `task_progress` al inicio
4. **SIEMPRE** usar el formato de commits estándar
5. **NUNCA** incluir archivos de debug o test en commits
6. **SIEMPRE** verificar el despliegue antes de dar por completada la tarea