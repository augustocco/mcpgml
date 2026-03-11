---
name: deployment-automation
description: Automate deployment workflow for MCPGML project. Use when deploying changes to production Kubernetes cluster.
---

# Deployment Automation for MCPGML

## Overview

This skill automates the complete deployment workflow for the MCPGML WordPress project running on Kubernetes. It handles downloading files from production, making changes locally, committing to Git, pushing to GitHub, deploying to the Kubernetes cluster, and verifying the deployment.

## Architecture

```
Local Development → GitHub → Kubernetes (EKS) → Production Pod
```

1. **Local Development**: Code modifications in `wordpress/` folder
2. **GitHub**: Version control repository
3. **Kubernetes**: EKS cluster with WordPress deployment
4. **Production Pod**: Running WordPress instance (`dp-prueba12`)

## Deployment Workflow

### Step 1: Download Files from Server (if needed)

When modifying existing files from production:

```bash
# Get the pod name
kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'

# Download specific file
kubectl cp -n plataformas <pod-name>:/var/www/html/path/to/file wordpress/wp-content/path/to/file

# Or download entire directory
kubectl cp -n plataformas <pod-name>:/var/www/html/wp-content/themes/eunolms wordpress/wp-content/themes/
```

**PowerShell Script Alternative:**

```powershell
# Download theme files from pod
$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
$remotePath = "/var/www/html/wp-content/themes/eunolms"
$localPath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\wp-content\themes\eunolms"

kubectl exec -n plataformas $podName -- sh -c "cd $remotePath && tar -czf - ." | tar -xzf - -C $localPath
```

### Step 2: Create/Modify Files Locally

New files should be created in the `wordpress/` folder structure following WordPress conventions:

```
wordpress/
├── wp-content/
│   ├── themes/
│   │   └── eunolms/
│   │       ├── functions.php
│   │       ├── style.css
│   │       ├── page-{slug}.php
│   │       ├── css/
│   │       │   └── *.css
│   │       └── js/
│   │       └── *.js
│   └── plugins/
│       └── plugin-name/
│           ├── plugin-name.php
│           └── includes/
```

**File Naming Conventions:**
- PHP files: `kebab-case.php` or `Class_Name.php`
- CSS files: `kebab-case.css`
- JS files: `kebab-case.js`

### Step 3: Commit and Push to GitHub

```bash
# Check git status
git status

# Add modified files
git add wordpress/wp-content/themes/eunolms/css/user-profile.css
git add wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/new-ability.php

# Or add all changes
git add .

# Commit with conventional commit format
git commit -m "fix(eunolms): eliminar icono duplicado en estado vacío"

# Push to remote repository
git push origin master
```

**Commit Message Format:**
```
type(scope): description

Types:
- feat: new feature
- fix: bug fix
- docs: documentation changes
- style: formatting (no logic changes)
- refactor: refactoring
- test: adding/updating tests
- chore: maintenance tasks

Scopes:
- eunolms: theme changes
- lmseu: MCP abilities plugin
- wordpress: WordPress core changes
- k8s: Kubernetes configuration
```

### Step 4: Deploy to Kubernetes Server

#### Option A: Using PowerShell Script (Recommended)

Use the existing `copy-theme.ps1` script:

```powershell
# Copy all theme files to the pod
powershell.exe -ExecutionPolicy Bypass -File copy-theme.ps1
```

**Script content (`copy-theme.ps1`):**
```powershell
$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
$themePath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\wp-content\themes\eunolms"

Get-ChildItem -Path $themePath -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($themePath.Length + 1).Replace('\', '/')
    Write-Host "Copiando: $relativePath"
    Get-Content $_.FullName | kubectl exec -i -n plataformas $podName -- sh -c "cat > /var/www/html/wp-content/themes/eunolms/$relativePath"
}

Write-Host "Copia completada"
```

#### Option B: Using kubectl cp

```bash
# Copy specific file
kubectl cp -n plataformas wordpress/wp-content/themes/eunolms/css/style.css \
  dp-prueba12-xxxxx-xxxxx:/var/www/html/wp-content/themes/eunolms/css/style.css

# Copy entire directory
kubectl cp -n plataformas wordpress/wp-content/themes/eunolms \
  dp-prueba12-xxxxx-xxxxx:/var/www/html/wp-content/themes/eunolms
```

#### Option C: Copying Plugin Files

```powershell
# Plugin copy script
$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
$pluginPath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\wp-content\plugins\lmseu-mcp-abilities"

Get-ChildItem -Path $pluginPath -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($pluginPath.Length + 1).Replace('\', '/')
    Write-Host "Copiando: $relativePath"
    Get-Content $_.FullName | kubectl exec -i -n plataformas $podName -- sh -c "cat > /var/www/html/wp-content/plugins/lmseu-mcp-abilities/$relativePath"
}

Write-Host "Copia completada"
```

### Step 5: Restart Deployment

After deploying files, restart the WordPress deployment to ensure all changes are applied:

```bash
# Check current deployment status
kubectl get deployment -n plataformas dp-prueba12

# Restart deployment by rolling out
kubectl rollout restart deployment -n plataformas dp-prueba12

# Wait for rollout to complete
kubectl rollout status deployment -n plataformas dp-prueba12

# Check pod status
kubectl get pods -n plataformas -l app=wpprueba12
```

**Expected Output:**
```
deployment.apps/dp-prueba12 restarted
Waiting for deployment "dp-prueba12" rollout to finish: 0 out of 1 new replicas have been updated...
Waiting for deployment "dp-prueba12" rollout to finish: 1 old replicas are pending termination...
deployment "dp-prueba12" successfully rolled out
NAME                           READY   STATUS    RESTARTS   AGE
dp-prueba12-6d489889b7-wvrgj   1/1     Running   0          1m
```

### Step 6: Verify Deployment

#### A. Verify Files are Deployed

```bash
# Check specific file exists on pod
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- \
  ls -la /var/www/html/wp-content/themes/eunolms/css/user-profile.css

# Verify file content (Windows PowerShell)
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- sh -c "cat /var/www/html/wp-content/themes/eunolms/css/user-profile.css" | Select-String -Pattern "tab-pane.active"
```

#### B. Check Pod Logs

```bash
# Check WordPress error logs
kubectl logs -n plataformas dp-prueba12-xxxxx-xxxxx --tail=100

# Follow logs in real-time
kubectl logs -n plataformas dp-prueba12-xxxxx-xxxxx -f
```

#### C. Test Functionality

1. **Access the application URL** in browser
2. **Navigate to the modified page/feature**
3. **Test the specific functionality** that was changed
4. **Check browser console** for JavaScript errors
5. **Verify CSS changes** are applied correctly

#### D. WordPress Admin Verification

```bash
# Clear WordPress cache via WP-CLI (if available)
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- \
  wp cache flush --allow-root

# Check plugin status
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- \
  wp plugin list --allow-root

# Check theme status
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- \
  wp theme list --allow-root
```

## Common Deployment Scenarios

### Scenario 1: CSS Styling Changes

```bash
# 1. Modify local CSS file
# Edit: wordpress/wp-content/themes/eunolms/css/user-profile.css

# 2. Commit and push
git add wordpress/wp-content/themes/eunolms/css/user-profile.css
git commit -m "style(eunolms): mejorar estilos de perfil de usuario"
git push origin master

# 3. Deploy to server
powershell.exe -ExecutionPolicy Bypass -File copy-theme.ps1

# 4. Restart deployment (optional for CSS changes, but recommended)
kubectl rollout restart deployment -n plataformas dp-prueba12

# 5. Verify in browser
```

### Scenario 2: New Feature Development

```bash
# 1. Create new PHP file
# Create: wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php

# 2. Add new JavaScript file
# Create: wordpress/wp-content/themes/eunolms/js/new-feature.js

# 3. Commit and push
git add wordpress/wp-content/
git commit -m "feat(lmseu): agregar nueva funcionalidad para gestión de usuarios"
git push origin master

# 4. Deploy to server (both theme and plugin)
# Use copy-theme.ps1 for theme files
# Use plugin-specific copy script for plugin files

# 5. Restart deployment
kubectl rollout restart deployment -n plataformas dp-prueba12

# 6. Verify functionality
```

### Scenario 3: Bug Fix

```bash
# 1. Download file from server if needed
kubectl cp -n plataformas dp-prueba12-xxxxx:/var/www/html/wp-content/themes/eunolms/functions.php \
  wordpress/wp-content/themes/eunolms/functions.php

# 2. Fix the bug locally
# Edit: wordpress/wp-content/themes/eunolms/functions.php

# 3. Commit and push
git add wordpress/wp-content/themes/eunolms/functions.php
git commit -m "fix(eunolms): corregir error en cálculo de progreso"
git push origin master

# 4. Deploy to server
powershell.exe -ExecutionPolicy Bypass -File copy-theme.ps1

# 5. Restart deployment
kubectl rollout restart deployment -n plataformas dp-prueba12

# 6. Verify fix works
```

## Quick Reference Commands

### Kubernetes
```bash
# Get pod name
kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'

# Get deployment status
kubectl get deployment -n plataformas dp-prueba12

# Restart deployment
kubectl rollout restart deployment -n plataformas dp-prueba12

# Check rollout status
kubectl rollout status deployment -n plataformas dp-prueba12

# View pod logs
kubectl logs -n plataformas dp-prueba12-xxxxx -f
```

### Git
```bash
# Check status
git status

# Commit changes
git add .
git commit -m "type(scope): description"

# Push to remote
git push origin master
```

### Deployment Scripts
```powershell
# Copy theme files
powershell.exe -ExecutionPolicy Bypass -File copy-theme.ps1

# Or inline
$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
Get-ChildItem -Path "wordpress\wp-content\themes\eunolms" -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($themePath.Length + 1).Replace('\', '/')
    Get-Content $_.FullName | kubectl exec -i -n plataformas $podName -- sh -c "cat > /var/www/html/wp-content/themes/eunolms/$relativePath"
}
```

## Troubleshooting

### Issue: Files Not Deploying

**Symptoms**: Changes not visible on production

**Solutions**:
1. Verify pod name is correct
2. Check file paths in copy script
3. Verify permissions on pod
4. Check kubectl connection to cluster

```bash
# Check pod is running
kubectl get pods -n plataformas -l app=wpprueba12

# Verify file exists on pod
kubectl exec -n platforms dp-prueba12-xxxxx -- ls -la /var/www/html/wp-content/themes/eunolms/
```

### Issue: Deployment Not Restarting

**Symptoms**: Pod stuck in restarting state

**Solutions**:
1. Check rollout status
2. Review pod logs
3. Verify deployment configuration

```bash
# Check rollout status
kubectl rollout status deployment -n plataformas dp-prueba12

# Check pod events
kubectl describe pod -n plataformas dp-prueba12-xxxxx

# Check logs
kubectl logs -n plataformas dp-prueba12-xxxxx --previous
```

### Issue: Git Push Failing

**Symptoms**: Cannot push to remote repository

**Solutions**:
1. Check remote URL
2. Verify credentials
3. Check network connection

```bash
# Check remote
git remote -v

# Test connection
git ls-remote origin
```

## Best Practices

### 1. Always Test Locally First
- Test CSS/JS changes in browser
- Verify PHP syntax
- Check for console errors

### 2. Use Descriptive Commit Messages
Follow conventional commits format for better tracking

### 3. Deploy During Low Traffic
- Schedule deployments during off-peak hours
- Notify users of scheduled maintenance if needed

### 4. Keep Backups
- Backup files before major changes
- Tag releases with git tags

```bash
# Create release tag
git tag -a v1.0.0 -m "Versión 1.0.0 - Corrección de iconos duplicados"
git push origin v1.0.0
```

### 5. Monitor After Deployment
- Watch logs for errors
- Monitor pod health
- Test critical functionality

### 6. Rollback Plan
Know how to quickly rollback if issues arise:

```bash
# Rollback Git
git reset --hard HEAD~1
git push -f origin master

# Redeploy previous version
powershell.exe -ExecutionPolicy Bypass -File copy-theme.ps1
kubectl rollout restart deployment -n plataformas dp-prueba12

# Or rollback deployment (if using deployment history)
kubectl rollout undo deployment -n plataformas dp-prueba12
```

## Resources

- **Kubernetes Documentation**: https://kubernetes.io/docs/
- **Git Documentation**: https://git-scm.com/doc
- **WordPress Codex**: https://developer.wordpress.org/
- **Project Repository**: https://github.com/augustocco/mcpgml
- **Kubernetes Config**: `wordpress12.yaml`
- **Deployment Script**: `copy-theme.ps1`