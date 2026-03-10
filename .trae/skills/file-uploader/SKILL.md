---
name: "file-uploader"
description: "Uploads files to EC2 and moves to EFS following the workflow from wordpress12.yaml. Invoke when user needs to copy or upload files to the WordPress deployment server."
---

# File Uploader

This skill manages the file upload workflow for the WordPress deployment dp-prueba12 in Kubernetes.

## Deployment Configuration (from wordpress12.yaml)

- **Deployment name**: `dp-prueba12`
- **Namespace**: `plataformas`
- **subPath**: `wpprueba12`
- **PVC**: `pvc-client-auteco`
- **EFS path**: `/var/efsAutecos/wpprueba12/`

## Upload Workflow

### 1. Upload files to EC2 (using pscp.exe)

Upload local files/directories to `/tmp` on EC2:

```powershell
& "C:\Program Files\PuTTY\pscp.exe" -batch -r -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" "local_path" "ubuntu@44.201.232.70:/tmp/destination"
```

**Important notes**:
- Use `-r` flag for recursive directory transfers
- Use `-batch` flag for non-interactive mode
- Use `-i` flag with the SSH key path
- Always upload to `/tmp` first due to system restrictions

### 2. Move files to EFS (using plink.exe)

Execute remote commands with sudo to move files to final EFS destination:

```powershell
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" "ubuntu@44.201.232.70" "sudo mv /tmp/source /var/efsAutecos/wpprueba12/destination"
```

**WordPress plugins path**: `/var/efsAutecos/wpprueba12/wp-content/plugins/`

**Apply permissions**:

```powershell
& "C:\Program Files\PuTTY\plink.exe" -batch -i "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk" "ubuntu@44.201.232.70" "sudo chmod 644 /var/efsAutecos/wpprueba12/path/to/file"
```

### 3. Apply changes in Kubernetes (using kubectl)

Restart the deployment to reload EFS content:

```powershell
kubectl rollout restart deployment dp-prueba12 -n plataformas
```

**IMPORTANT**:
- Only use `kubectl rollout restart` for applying changes
- Never use `kubectl cp` directly to the container
- Always restart the deployment after moving files to EFS

## Common File Destinations

- **WordPress plugins**: `/var/efsAutecos/wpprueba12/wp-content/plugins/`
- **WordPress themes**: `/var/efsAutecos/wpprueba12/wp-content/themes/`
- **WordPress uploads**: `/var/efsAutecos/wpprueba12/wp-content/uploads/`
- **WordPress root files**: `/var/efsAutecos/wpprueba12/`

## Rules

1. **ALWAYS** read `d:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress12.yaml` before performing any operations
2. **NEVER** use `kubectl cp` to copy files directly to the container
3. **NEVER** execute additional kubectl commands beyond `kubectl rollout restart`
4. **ALWAYS** upload to `/tmp` on EC2 first, then move to EFS with sudo
5. **ALWAYS** ask for authorization before running any kubectl commands
6. **NEVER** modify the deployment configuration unless explicitly requested

## Troubleshooting

- If pscp fails with "unable to open", ensure the `/tmp` destination directory exists and has proper permissions
- Use `sudo rm -rf /tmp/destination && sudo mkdir -p /tmp/destination && sudo chmod 777 /tmp/destination` to clean and recreate directories
- Verify EFS path matches the subPath from wordpress12.yaml (`wpprueba12`)
- Always verify file structure in EFS after moving files
