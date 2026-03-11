# Script para copiar solo archivos modificados al servidor EC2 (EFS)
# Uso: powershell.exe -ExecutionPolicy Bypass -File copy-changed-files.ps1
# Nota: Este script usa pscp y plink de PuTTY para subir archivos al servidor EC2

# Configuración
$pscpPath = "C:\Program Files\PuTTY\pscp.exe"
$plinkPath = "C:\Program Files\PuTTY\plink.exe"
$keyPath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KEYS\KEY-WPLMSEUNO.ppk"
$ec2User = "ubuntu"
$ec2Host = "44.201.232.70"
$efsPath = "/var/efsAutecos/wpprueba12"

# Archivos modificados (actualizar esta lista según sea necesario)
# Obtener archivos modificados con: git diff --name-only HEAD~1 HEAD
$changedFiles = @(
    "wordpress/wp-content/themes/eunolms/js/user-profile.js",
    "wordpress/wp-content/themes/eunolms/css/user-profile.css"
)

Write-Host "=== Copiando archivos modificados al servidor EC2 (EFS) ===" -ForegroundColor Green
Write-Host ""

foreach ($file in $changedFiles) {
    if (-not (Test-Path $file)) {
        Write-Host "✗ Archivo no encontrado: $file" -ForegroundColor Red
        continue
    }
    
    Write-Host "Procesando: $file" -ForegroundColor Yellow
    
    # Obtener el nombre del archivo
    $fileName = Split-Path -Leaf $file
    
    # Obtener la ruta relativa (sin la carpeta wordpress/)
    $relativePath = $file -replace "^wordpress/", ""
    
    # Construir la ruta destino en EFS
    $destPath = "$efsPath/$relativePath"
    
    # Subir archivo a /tmp usando pscp
    Write-Host "  Subiendo a /tmp..." -ForegroundColor Cyan
    & $pscpPath -batch -i $keyPath $file "$ec2User@$ec2Host`:/tmp/$fileName"
    
    if ($LASTEXITCODE -eq 0) {
        # Mover archivo a destino con sudo usando plink
        Write-Host "  Moviendo a EFS con sudo..." -ForegroundColor Cyan
        & $plinkPath -batch -i $keyPath "$ec2User@$ec2Host" "sudo mv /tmp/$fileName $destPath && sudo chmod 644 $destPath"
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host "  ✓ Copiado exitosamente" -ForegroundColor Green
        } else {
            Write-Host "  ✗ Error al mover archivo con sudo" -ForegroundColor Red
        }
    } else {
        Write-Host "  ✗ Error al subir archivo" -ForegroundColor Red
    }
    
    Write-Host ""
}

Write-Host "=== Copia completada ===" -ForegroundColor Green
Write-Host ""
Write-Host "Para reiniciar el deployment, ejecuta:" -ForegroundColor Cyan
Write-Host "kubectl rollout restart deployment dp-prueba12 -n plataformas" -ForegroundColor Yellow
Write-Host ""
Write-Host "Para verificar el rollout, ejecuta:" -ForegroundColor Cyan
Write-Host "kubectl rollout status deployment dp-prueba12 -n plataformas" -ForegroundColor Yellow
