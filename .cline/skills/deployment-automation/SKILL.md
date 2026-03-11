---
name: deployment-automation
description: Automate deployment workflow for MCPGML project. Use when deploying changes to production Kubernetes cluster.
---

# Deployment Automation for MCPGML

## Overview

This skill automates the complete deployment workflow for the MCPGML WordPress project running on Kubernetes. It handles downloading files from production, making changes locally, committing to Git, pushing to GitHub, deploying to the Kubernetes cluster, and verifying the deployment.

## Architecture

```
Local Development → GitHub → EC2 Server (EFS) → Kubernetes (EKS) → Production Pod
```

1. **Local Development**: Code modifications in `wordpress/` folder
2. **GitHub**: Version control repository
3. **EC2 Server**: Ubuntu instance with EFS mounted at `/var/efsAutecos/wpprueba12/`
4. **Kubernetes**: EKS cluster with WordPress deployment (`dp-prueba12`)
5. **Production Pod**: Running WordPress instance (`dp-prueba12`) mounting EFS volume

**Important**: The deployment uses a shared EFS volume mounted on an EC2 instance. Files must be uploaded to `/tmp` on EC2 first (due to permissions), then moved to EFS with sudo, and finally the deployment is restarted.

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

**IMPORTANT**: The deployment uses a shared EFS volume mounted on an EC2 instance. Files must be uploaded to `/tmp` on EC2 first (due to permissions), then moved to EFS with sudo.

#### Method: Upload via PuTTY (pscp) and plink

**Server Details:**
- EC2 IP: `44.201.232.70`
- User: `ubuntu`
- Key: `D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk`
- EFS Path: `/var/efsAutecos/wpprueba12/`

##### Step 4.1: Upload Files to EC2 Server (to /tmp)

Use `pscp.exe` from PuTTY to upload files to the temporary folder on EC2:

```powershell
# Upload a single file to /tmp
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/css/user-profile.css" `
  "ubuntu@44.201.232.70:/tmp/user-profile.css"

# Upload multiple files
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/js/user-profile.js" `
  "ubuntu@44.201.232.70:/tmp/user-profile.js"
```

##### Step 4.2: Move Files to EFS (with sudo)

Use `plink.exe` to execute commands on the remote server with sudo privileges:

```powershell
# Move CSS file to theme folder
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/user-profile.css /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/user-profile.css"

# Set correct permissions
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/user-profile.css"

# Move JS file
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/user-profile.js /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/js/user-profile.js"

& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/js/user-profile.js"
```

##### Step 4.3: Upload Plugin Files

```powershell
# Upload plugin file
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php" `
  "ubuntu@44.201.232.70:/tmp/new-feature.php"

# Move to plugin folder
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/new-feature.php /var/efsAutecos/wpprueba12/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php"

& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php"
```

#### Automated Deployment Script (deploy.ps1)

Create a PowerShell script to automate the entire deployment process:

```powershell
# deploy.ps1 - Deployment automation script
$pscpPath = "C:\Program Files\PuTTY\pscp.exe"
$plinkPath = "C:\Program Files\PuTTY\plink.exe"
$keyPath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk"
$ec2User = "ubuntu"
$ec2Host = "44.201.232.70"
$efsPath = "/var/efsAutecos/wpprueba12"

# Files to deploy (update this list as needed)
$filesToDeploy = @{
    "wordpress/wp-content/themes/eunolms/css/user-profile.css" = "wp-content/themes/eunolms/css/user-profile.css"
    "wordpress/wp-content/themes/eunolms/js/user-profile.js" = "wp-content/themes/eunolms/js/user-profile.js"
}

Write-Host "=== Starting Deployment ===" -ForegroundColor Green

foreach ($localFile in $filesToDeploy.Keys) {
    $remoteFile = $filesToDeploy[$localFile]
    $tempFile = Split-Path -Leaf $localFile
    $destPath = "$efsPath/$remoteFile"
    
    Write-Host "Uploading: $localFile" -ForegroundColor Yellow
    
    # Upload to /tmp
    & $pscpPath -batch -i $keyPath $localFile "$ec2User@$ec2Host`:/tmp/$tempFile"
    
    if ($LASTEXITCODE -eq 0) {
        # Move to destination with sudo
        & $plinkPath -batch -i $keyPath "$ec2User@$ec2Host" "sudo mv /tmp/$tempFile $destPath"
        & $plinkPath -batch -i $keyPath "$ec2User@$ec2Host" "sudo chmod 644 $destPath"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✓ Successfully deployed" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Failed to move file" -ForegroundColor Red
        }
    } else {
        Write-Host "  ✗ Failed to upload" -ForegroundColor Red
    }
}

Write-Host "=== Deployment Complete ===" -ForegroundColor Green
Write-Host ""
Write-Host "To restart the deployment, run:" -ForegroundColor Cyan
Write-Host "kubectl rollout restart deployment dp-prueba12 -n plataformas" -ForegroundColor Yellow
```

**Usage:**
```powershell
powershell.exe -ExecutionPolicy Bypass -File deploy.ps1
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
# Check file exists on NFS server
kubectl exec -it nfs-server-pl-7bd8cb75c6-m6tdr -- \
  ls -la /exports/wpprueba/wp-content/themes/eunolms/css/user-profile.css

# Check file exists on WordPress pod
kubectl exec -n plataformas dp-prueba12-xxxxx-xxxxx -- \
  ls -la /var/www/html/wp-content/themes/eunolms/css/user-profile.css

# Verify file content
kubectl exec -it nfs-server-pl-7bd8cb75c6-m6tdr -- sh -c "cat /exports/wpprueba/wp-content/themes/eunolms/css/user-profile.css"
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

```powershell
# 1. Modify local CSS file
# Edit: wordpress/wp-content/themes/eunolms/css/user-profile.css

# 2. Commit and push
git add wordpress/wp-content/themes/eunolms/css/user-profile.css
git commit -m "style(eunolms): mejorar estilos de perfil de usuario"
git push origin master

# 3. Upload to EC2 server using pscp
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/css/user-profile.css" `
  "ubuntu@44.201.232.70:/tmp/user-profile.css"

# 4. Move to EFS with sudo using plink
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/user-profile.css /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/user-profile.css && sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/user-profile.css"

# 5. Restart deployment to apply changes
kubectl rollout restart deployment dp-prueba12 -n plataformas

# 6. Verify in browser
```

### Scenario 2: New Feature Development

```powershell
# 1. Create new PHP file
# Create: wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php

# 2. Add new JavaScript file
# Create: wordpress/wp-content/themes/eunolms/js/new-feature.js

# 3. Commit and push
git add wordpress/wp-content/
git commit -m "feat(lmseu): agregar nueva funcionalidad para gestión de usuarios"
git push origin master

# 4. Upload JS file to EC2
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/js/new-feature.js" `
  "ubuntu@44.201.232.70:/tmp/new-feature.js"

# 5. Upload PHP file to EC2
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php" `
  "ubuntu@44.201.232.70:/tmp/new-feature.php"

# 6. Move files to EFS with sudo
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/new-feature.js /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/js/new-feature.js && sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/js/new-feature.js"

& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/new-feature.php /var/efsAutecos/wpprueba12/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php && sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/plugins/lmseu-mcp-abilities/includes/new-feature.php"

# 7. Restart deployment
kubectl rollout restart deployment dp-prueba12 -n plataformas

# 8. Verify functionality
```

### Scenario 3: Bug Fix

```powershell
# 1. Download file from EFS server if needed
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo cat /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/functions.php" > wordpress/wp-content/themes/eunolms/functions.php

# 2. Fix the bug locally
# Edit: wordpress/wp-content/themes/eunolms/functions.php

# 3. Commit and push
git add wordpress/wp-content/themes/eunolms/functions.php
git commit -m "fix(eunolms): corregir error en cálculo de progreso"
git push origin master

# 4. Upload to EC2
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/functions.php" `
  "ubuntu@44.201.232.70:/tmp/functions.php"

# 5. Move to EFS with sudo
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/functions.php /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/functions.php && sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/functions.php"

# 6. Restart deployment
kubectl rollout restart deployment dp-prueba12 -n plataformas

# 7. Verify fix works
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

### Deployment Commands (PuTTY - pscp/plink)
```powershell
# Upload single file to EC2
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "wordpress/wp-content/themes/eunolms/css/style.css" `
  "ubuntu@44.201.232.70:/tmp/style.css"

# Move file to EFS with sudo
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo mv /tmp/style.css /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/style.css && sudo chmod 644 /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/css/style.css"

# Deploy multiple files using script
powershell.exe -ExecutionPolicy Bypass -File copy-changed-files.ps1

# Download file from EFS
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo cat /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/functions.php" > wordpress/wp-content/themes/eunolms/functions.php
```

## Troubleshooting

### Issue: Files Not Deploying

**Symptoms**: Changes not visible on production

**Solutions**:
1. Verify EC2 connection with PuTTY
2. Check file paths in script
3. Verify SSH key permissions
4. Check EFS mount on EC2
5. Verify sudo passwordless access for ubuntu user

```powershell
# Test SSH connection to EC2
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "echo 'Connection successful'"

# Check if EFS is mounted
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "df -h | grep efs"

# Verify file exists on EFS
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "ls -la /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/"
```

### Issue: PSCP Upload Failing

**Symptoms**: pscp.exe cannot upload files to EC2

**Solutions**:
1. Verify PuTTY installation path
2. Check SSH key format (.ppk)
3. Verify network connectivity
4. Check EC2 security group allows SSH (port 22)
5. Verify file exists locally

```powershell
# Test pscp with a simple file
& "C:\Program Files\PuTTY\pscp.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "README.md" "ubuntu@44.201.232.70:/tmp/test-upload.txt"

# Check if file was uploaded
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "ls -la /tmp/test-upload.txt"
```

### Issue: Sudo Commands Failing

**Symptoms**: Cannot move files from /tmp to EFS with sudo

**Solutions**:
1. Verify ubuntu user has sudo privileges
2. Check /tmp directory permissions
3. Verify EFS directory permissions
4. Check sudoers configuration

```powershell
# Test sudo access
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "sudo whoami"

# Check EFS directory permissions
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "ls -la /var/efsAutecos/wpprueba12/wp-content/themes/eunolms/"

# Check /tmp permissions
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" `
  "ubuntu@44.201.232.70" "ls -la /tmp/"
```

### Issue: Deployment Not Restarting

**Symptoms**: Pod stuck in restarting state or changes not applying

**Solutions**:
1. Check rollout status
2. Review pod logs
3. Verify deployment configuration
4. Ensure EFS volume is properly mounted

```bash
# Check rollout status
kubectl rollout status deployment -n plataformas dp-prueba12

# Check pod events
kubectl describe pod -n plataformas dp-prueba12-xxxxx

# Check logs
kubectl logs -n plataformas dp-prueba12-xxxxx --previous

# Verify EFS mount in pod
kubectl exec -n plataformas dp-prueba12-xxxxx -- df -h | grep html
```

### Issue: Git Push Failing

**Symptoms**: Cannot push to remote repository

**Solutions**:
1. Check remote URL
2. Verify credentials
3. Check network connection
4. Verify branch name

```bash
# Check remote
git remote -v

# Test connection
git ls-remote origin

# Verify current branch
git branch

# Check if origin/master exists
git branch -r
```

### Issue: Changes Not Visible in Browser

**Symptoms**: Files deployed but changes not visible

**Solutions**:
1. Clear browser cache (Ctrl+F5)
2. Check WordPress cache plugins
3. Verify deployment was restarted
4. Check CDN if applicable

```bash
# Clear WordPress cache via WP-CLI
kubectl exec -n plataformas dp-prueba12-xxxxx -- wp cache flush --allow-root

# Check if pod restarted successfully
kubectl get pods -n plataformas -l app=wpprueba12 --sort-by=.metadata.creationTimestamp
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