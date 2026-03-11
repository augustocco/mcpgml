# Configuración del Proyecto MCPGML

## Información General del Sitio

- **URL del sitio**: https://eks12.lmseunoconsulting.com/
- **Nombre del deployment**: dp-prueba12
- **Base de datos**: wpprueba12
- **Namespace**: plataformas
- **Versión PHP**: 8.4
- **Sistema**: WordPress con LearnDash

## Arquitectura

### Stack Tecnológico
- **Frontend**: WordPress con tema personalizado `eunolms`
- **Backend**: PHP 8.4
- **Orquestación**: Kubernetes (EKS)
- **Base de datos**: MariaDB (mariadb-pruebas)
- **Autoscaling**: HorizontalPodAutoscaler
- **Storage**: PersistentVolumeClaim (pvc-client-auteco)

### Infraestructura
- **Cloud Provider**: AWS (Elastic Kubernetes Service)
- **Tamaño VM**: EC2
- **Resources**:
  - Memory: 2Gi requests / 4Gi limits
  - CPU: 1 vCPU requests / 2 vCPU limits

## Deployment Details

### Archivo de Configuración
- **Archivo**: `wordpress12.yaml`
- **Location**: `D:/PROYECTOS TI2/EUNO/PLATAFORMAS/AWS/KUBERNETES/pruebas2025/MCPGML/`

### Componentes del Deployment

#### 1. WordPress Container
- **Image**: `wordpress:php8.4-apache`
- **Port**: 80
- **Environment Variables**:
  - `WORDPRESS_DB_HOST`: mariadb-pruebas
  - `WORDPRESS_DB_NAME`: wpprueba12
  - `WORDPRESS_DB_PASSWORD`: (secreto en pl-secrets)
  - `WORDPRESS_DB_USER`: (secreto en pl-secrets)
  - `WORDPRESS_CONFIG_EXTRA`: $_SERVER['HTTPS'] = 'on';define('FS_METHOD', 'direct');

#### 2. Volumes
- **wp-volmnt1**: PVC `pvc-client-auteco` con subPath `wpprueba12`
- **httpd-config-volume**: ConfigMap `wordpress-config` (httpd.conf)
- **php-config-volume**: ConfigMap `wordpress-config` (php.ini)

#### 3. Service
- **Name**: svc-wpprueba12
- **Type**: ClusterIP
- **Port**: 80
- **Selector**: app=wpprueba12

#### 4. HPA (Horizontal Pod Autoscaler)
- **Name**: hpa-wpprueba12
- **Min Replicas**: 1
- **Max Replicas**: 4
- **Target CPU**: 80% average utilization

## Plugins Activos

### MCPGML Plugins
1. **lmseu-mcp-abilities**: Habilidades MCP personalizadas para WordPress
2. **mcp-adapter**: Adaptador para integración con MCP Protocol
3. **abilities-api**: WordPress Abilities API

### LearnDash Plugins
4. **sfwd-lms**: Sistema de gestión de aprendizaje (LMS)

## Theme Personalizado

- **Theme**: `eunolms`
- **Location**: `wordpress/wp-content/themes/eunolms/`
- **Funcionalidades**:
  - Página de perfil de usuario (`page-mi-perfil.php`)
  - Sistema de menús personalizado
  - Assets CSS y JS personalizados

## Comandos de Referencia

### Kubernetes Commands
```bash
# Ver deployments
kubectl get deployments -n plataformas

# Ver pods
kubectl get pods -n plataformas -l app=wpprueba12

# Ver logs
kubectl logs -n plataformas -l app=wpprueba12 -f

# Escalar deployment
kubectl scale deployment dp-prueba12 -n plataformas --replicas=2

# Ver HPA
kubectl get hpa -n plataformas
```

### Git Commands
```bash
# Ver estado
git status

# Commits
git add .
git commit -m "tipo(alcance): descripción breve"
git push origin main
```

### WordPress Commands
```bash
# Activar/desactivar plugin
wp plugin activate lmseu-mcp-abilities -n

# Limpiar cache
wp cache flush

# Actualizar base de datos
wp core update-db
```

## Comandos de Despliegue

### Desplegar a Kubernetes
```bash
kubectl apply -f wordpress12.yaml -n plataformas
kubectl rollout status deployment/dp-prueba12 -n plataformas
```

### Copiar archivos de cambios
```bash
# Plugins
.\copy-changed-files.ps1 -Source "wordpress/wp-content/plugins/lmseu-mcp-abilities" -Destination "wordpress/" -Pattern "*.*"

# Theme
.\copy-changed-files.ps1 -Source "wordpress/wp-content/themes/eunolms" -Destination "wordpress/" -Pattern "*.*"

# Desplegar
kubectl apply -f wordpress12.yaml -n plataformas
```

## Información de Contacto

- **Email**: soporte@eunoconsulting.com
- **Web**: https://eunoconsulting.com

## Notas Importantes

- **HTTPS**: Forzado mediante configuración de WordPress
- **FS_METHOD**: Configurado en 'direct' para uploads directos
- **Backup**: El PVC `pvc-client-auteco` maneja persistencia de datos
- **Logs**: Los logs están disponibles en el pod del deployment

## Última Actualización

- **Fecha**: 2026-03-10
- **Último Commit**: 42e1eae83ae46b60917fd1530846c4a0b792567d
- **URL Repository**: https://github.com/augustocco/mcpgml.git