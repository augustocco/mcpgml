param(
    [string]$Message = "Sync CSV changes with sudo",
    [switch]$FullUpload = $false
)

$ROOT = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCP"
$WP_CONTENT_LOCAL = Join-Path $ROOT "wp-content"
$WP_CONTENT_REMOTE = "/var/efsAutecos/wpprueba10/wp-content"
$SSH_KEY = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk"
$SSH_HOST = "ubuntu@44.201.232.70"

Write-Host "Iniciando despliegue seguro con SUDO..." -ForegroundColor Cyan

# 1. Obtener archivos modificados
$modifiedFiles = git status --porcelain | Where-Object { $_ -match "wp-content/" } | ForEach-Object { 
    $_.Substring(3).Trim().Trim('"')
}

# 2. Sincronizar Git
Write-Host "Sincronizando cambios en GitHub..."
git add .
try { git commit -m $Message -q } catch { }
git push origin main -q

# 3. Preparar archivos a subir (Si no hay modificados, forzamos los del reporte)
$filesToUpload = if ($modifiedFiles) { $modifiedFiles } else { 
    @(
        "wp-content/plugins/lmseu-mcp-abilities/includes/class-reports-dashboard.php",
        "wp-content/plugins/lmseu-mcp-abilities/includes/class-learndash-abilities.php"
    )
}

# 4. Transferencia via TMP
Write-Host "Subiendo $(($filesToUpload | Measure-Object).Count) archivos..." -ForegroundColor Green
foreach ($file in $filesToUpload) {
    $localPath = Join-Path $ROOT $file
    if (Test-Path $localPath) {
        $filename = Split-Path $localPath -Leaf
        $remoteFinalPath = "/var/efsAutecos/wpprueba10/" + $file.Replace("\", "/")
        $remoteFinalDir = ($remoteFinalPath.Substring(0, $remoteFinalPath.LastIndexOf('/')))
        $tmpPath = "/tmp/$filename"

        Write-Host "  -> $file" -ForegroundColor Gray
        
        # 1. Subir a TMP (donde hay permiso)
        & "C:\Program Files\PuTTY\pscp.exe" -batch -i $SSH_KEY "$localPath" "$SSH_HOST`:$tmpPath"
        
        # 2. Mover con SUDO a destino final
        & "C:\Program Files\PuTTY\plink.exe" -batch -i $SSH_KEY $SSH_HOST "sudo mkdir -p $remoteFinalDir && sudo mv $tmpPath $remoteFinalPath && sudo chmod 644 $remoteFinalPath"
    }
}

# 5. Reiniciar Kubernetes
Write-Host "Reiniciando Pods en Kubernetes..."
kubectl rollout restart deployment dp-prueba10 -n plataformas

Write-Host "Despliegue finalizado exitosamente." -ForegroundColor Green