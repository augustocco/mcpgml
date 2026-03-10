@echo off
REM Wrapper script to connect to WordPress MCP server via STDIO
REM This script uses kubectl to execute WP-CLI commands in the Kubernetes pod

set DEPLOYMENT_NAME=wpprueba12
set NAMESPACE=plataformas
set USER=augustocco

REM Find the pod using the deployment label
for /f "tokens=1" %%i in ('kubectl get pod -n %NAMESPACE% -l app=%DEPLOYMENT_NAME% -o jsonpath="{.items[0].metadata.name}"') do set POD=%%i

if "%POD%"=="" (
    echo Error: Pod not found
    exit /b 1
)

REM Execute WP-CLI command to start MCP STDIO server
kubectl exec -n %NAMESPACE% %POD% -- wp mcp-adapter serve --server=mcp-adapter-default-server --user=%USER%
