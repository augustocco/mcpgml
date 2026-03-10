$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
$themePath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\wp-content\themes\eunolms"

Get-ChildItem -Path $themePath -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($themePath.Length + 1).Replace('\', '/')
    Write-Host "Copiando: $relativePath"
    Get-Content $_.FullName | kubectl exec -i -n plataformas $podName -- sh -c "cat > /var/www/html/wp-content/themes/eunolms/$relativePath"
}

Write-Host "Copia completada"
