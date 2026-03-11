#!/usr/bin/env bash
# deploy-plugin.sh — Sube el plugin lmseu-mcp-abilities al servidor EC2
# Uso: bash deploy-plugin.sh

set -e

PLUGIN_LOCAL="d:/PROYECTOS TI2/EUNO/PLATAFORMAS/AWS/KUBERNETES/pruebas2025/MCP/lmseu-mcp-abilities"
PLUGIN_REMOTE="/var/efsAutecos/wpprueba10/wp-content/plugins/lmseu-mcp-abilities"
SSH_KEY="D:/PROYECTOS TI2/EUNO/PLATAFORMAS/AWS/KEYS/KEY-WPLMSEUNO.ppk"
SSH_HOST="ubuntu@44.201.232.70"
PSCP="/c/Program Files/PuTTY/pscp.exe"
PLINK="/c/Program Files/PuTTY/plink.exe"

FILES=(
    "lmseu-mcp-abilities.php"
    "includes/class-support-abilities.php"
    "includes/class-learndash-abilities.php"
    "includes/class-elementor-abilities.php"
)

echo "==> Subiendo archivos al servidor..."
for FILE in "${FILES[@]}"; do
    FILENAME=$(basename "$FILE")
    SUBDIR=$(dirname "$FILE")

    # Subir a /tmp
    "$PSCP" -batch -i "$SSH_KEY" \
        "$PLUGIN_LOCAL/$FILE" \
        "$SSH_HOST:/tmp/$FILENAME"

    # Mover con sudo al destino correcto
    if [ "$SUBDIR" = "." ]; then
        DEST="$PLUGIN_REMOTE/$FILENAME"
    else
        DEST="$PLUGIN_REMOTE/$SUBDIR/$FILENAME"
    fi

    echo "" | "$PLINK" -batch -i "$SSH_KEY" "$SSH_HOST" \
        "sudo cp /tmp/$FILENAME $DEST"

    echo "    OK: $FILE"
done

echo "==> Deploy completado."