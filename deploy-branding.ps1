# Script de despliegue para Branding Multicliente
# Despliega los archivos nuevos del branding al servidor EFS

$pscpPath = "C:\Program Files\PuTTY\pscp.exe"
$plinkPath = "C:\Program Files\PuTTY\plink.exe"
$keyPath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk"
$ec2User = "ubuntu"
$ec2Host = "44.201.232.70"
$efsPath = "/var/efsAutecos/wpprueba12"

# Archivos a desplegar
$filesToDeploy = @{
    "wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/class-client-branding-manager.php" = "wp-content/plugins/lmseu-mcp-abilities/includes/class-client-branding-manager.php"
    "wordpress/wp-content/plugins/lmseu-mcp-abilities/includes/class-client-branding-meta-box.php" = "wp-content/plugins/lmseu-mcp-abilities/includes/class-client-branding-meta-box.php"
    "wordpress/wp-content/plugins/lmseu-mcp-abilities/css/euno-branding-meta-box.css" = "wp-content/plugins/lmseu-mcp-abilities/css/euno-branding-meta-box.css"
    "wordpress/wp-content/plugins/lmseu-mcp-abilities/js/euno-branding-meta-box.js" = "wp-content/plugins/lmseu-mcp-abilities/js/euno-branding-meta-box.js"
    "wordpress/wp-content/plugins/lmseu-mcp-abilities/lmseu-mcp-abilities.php" = "wp-content/plugins/lmseu-mcp-abilities/lmseu-mcp-abilities.php"
    "wordpress/wp-content/themes/eunolms/header.php" = "wp-content/themes/eunolms/header.php"
}

Write-Host "=== Iniciando Despliegue de Branding Multicliente ===" -ForegroundColor Green
Write-Host ""

$successCount = 0
$failCount = 0

foreach ($localFile in $filesToDeploy.Keys) {
    $remoteFile = $filesToDeploy[$localFile]
    $tempFile = Split-Path -Leaf $localFile
    $destPath = "$efsPath/$remoteFile"
    
    Write-Host "Subiendo: $localFile" -ForegroundColor Yellow
    
    # Upload to /tmp
    & $pscpPath -batch -i $keyPath $localFile "$ec2User@$ec2Host`:/tmp/$tempFile"
    
    if ($LASTEXITCODE -eq 0) {
        # Move to destination with sudo
        & $plinkPath -batch -i $keyPath "$ec2User@$ec2Host" "sudo mv /tmp/$tempFile $destPath"
        & $plinkPath -batch -i $keyPath "$ec2User@$ec2Host" "sudo chmod 644 $destPath"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  Desplegado exitosamente" -ForegroundColor Green
            $successCount++
        } else {
            Write-Host "  Error al mover archivo a EFS" -ForegroundColor Red
            $failCount++
        }
    } else {
        Write-Host "  Error al subir archivo" -ForegroundColor Red
        $failCount++
    }
    Write-Host ""
}

Write-Host "=== Resumen del Despliegue ===" -ForegroundColor Cyan
Write-Host "Exitos: $successCount" -ForegroundColor Green
Write-Host "Fallos: $failCount" -ForegroundColor Red
Write-Host ""

if ($successCount -gt 0) {
    Write-Host "=== Reiniciando Deployment ===" -ForegroundColor Yellow
    Write-Host "Ejecutando: kubectl rollout restart deployment dp-prueba12 -n plataformas" -ForegroundColor White
    
    kubectl rollout restart deployment -n plataformas dp-prueba12
    
    Write-Host ""
    Write-Host "Esperando que el rollout se complete..." -ForegroundColor Cyan
    kubectl rollout status deployment -n plataformas dp-prueba12
    
    Write-Host ""
    Write-Host "=== Despliegue Completado ===" -ForegroundColor Green
    Write-Host ""
    Write-Host "Para verificar, ejecute:" -ForegroundColor White
    Write-Host "kubectl get pods -n plataformas -l app=wpprueba12" -ForegroundColor Yellow
} else {
    Write-Host "ERROR: No se pudo desplegar ningun archivo. Verifique los logs." -ForegroundColor Red
}