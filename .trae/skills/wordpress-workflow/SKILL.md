---
name: "wordpress-workflow"
description: "Automatiza el flujo de trabajo de desarrollo de WordPress: descargar, modificar, subir a GitHub, desplegar y validar cambios. Invoke cuando se realicen cambios o configuraciones en WordPress."
---

# WordPress Workflow Skill

Este skill automatiza el flujo de trabajo completo para realizar cambios en WordPress en el servidor Kubernetes.

## Flujo de Trabajo

Cuando se necesite hacer cambios en WordPress (modificar plugins, temas, configuración, etc.), seguir este proceso:

### 1. Descargar Archivos del Servidor

Si necesitas modificar archivos existentes en el servidor, primero descárgalos al directorio local:
```
d:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\
```

Para descargar archivos del servidor Kubernetes:
```bash
kubectl cp -n wordpress <pod-name>:/path/to/file d:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\path\to\file
```

### 2. Realizar Modificaciones

Modifica los archivos necesarios en el directorio local:
```
d:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\
```

### 3. Subir Cambios a GitHub

Sube los cambios al repositorio:
```
https://github.com/augustocco/mcpgml.git
```

Comandos a ejecutar:
```bash
git add .
git commit -m "Descripción del cambio"
git push origin main
```

### 4. Desplegar en el Servidor

Despliega los cambios en el servidor Kubernetes. Esto puede implicar:
- Actualizar la imagen del contenedor
- Aplicar cambios en configuraciones
- Reiniciar deployments/pods

### 5. Validar Funcionamiento

Verifica que los cambios funcionen correctamente:
- Ejecuta pruebas relevantes
- Valida la funcionalidad afectada
- Verifica logs si es necesario
- Confirma que el MCP reconoce los cambios (si aplica)

## Directorios Clave

- **Local:** `d:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\`
- **WordPress plugins:** `wordpress/wp-content/plugins/`
- **WordPress temas:** `wordpress/wp-content/themes/`
- **Repositorio GitHub:** `https://github.com/augustocco/mcpgml.git`

## Cuando Usar Este Skill

- Crear o modificar habilidades del MCP
- Actualizar plugins de WordPress
- Modificar configuraciones del servidor
- Implementar nuevas funcionalidades
- Corregir errores o bugs
- Cualquier cambio que afecte al servidor WordPress

## Consideraciones

- Siempre descargar archivos del servidor antes de modificarlos
- Commits descriptivos en GitHub
- Validar cambios en desarrollo antes de producción
- Verificar que el servidor Kubernetes esté accesible
- Revisar logs después de desplegar cambios
