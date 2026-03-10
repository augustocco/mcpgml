# WordPress Workflow

Modificar: Descargar a `wordpress\`. Crear: En `wordpress\`. GitHub: Commit+Push antes.

## Copia Eficiente al Servidor

Para copiar archivos al servidor de manera eficiente:

```powershell
# Método recomendado: Script de PowerShell para copia masiva
$podName = kubectl get pods -n plataformas -l app=wpprueba12 -o jsonpath='{.items[0].metadata.name}'
$themePath = "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\wordpress\wp-content\themes\nombre-tema"

Get-ChildItem -Path $themePath -Recurse -File | ForEach-Object {
    $relativePath = $_.FullName.Substring($themePath.Length + 1).Replace('\', '/')
    Get-Content $_.FullName | kubectl exec -i -n plataformas $podName -- sh -c "cat > /var/www/html/wp-content/themes/nombre-tema/$relativePath"
}
```

O crear script `copy-theme.ps1` y ejecutar:
`powershell -ExecutionPolicy Bypass -File "D:\PROYECTOS TI2\EUNO\PLATAFORMAS\AWS\KUBERNETES\pruebas2025\MCPGML\copy-theme.ps1"`

## Comandos Esenciales

Reiniciar: `kubectl rollout restart deployment dp-prueba12 -n plataformas`

Validar: Probar cambio y logs.

NO modificar sin local. NO desplegar sin GitHub+Push. SIEMPRE reiniciar deployment. SIEMPRE validar cambios.
