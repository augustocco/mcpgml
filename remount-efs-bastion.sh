#!/bin/bash
# remount-efs-bastion.sh
# Obtiene los IPs actuales de los pods NFS y remonta el EFS en el bastion.
# Usar cuando los pods NFS se hayan reiniciado y el bastion pierda los mounts.
#
# Uso: bash remount-efs-bastion.sh

KEY="D:/PROYECTOS TI2/EUNO/PLATAFORMAS/AWS/KEYS/KEY-WPLMSEUNO.pem"
BASTION="ubuntu@44.201.232.70"
SSH_OPTS="-o StrictHostKeyChecking=no -o ConnectTimeout=15"

echo "=== Obteniendo IPs actuales de pods NFS ==="

IP_AUTECO=$(kubectl get pod -n default -l app=nfs-server-auteco \
  -o jsonpath='{.items[0].status.podIP}' 2>/dev/null)
IP_PLATAFORMAS=$(kubectl get pod -n default -l app=nfs-server-pl \
  -o jsonpath='{.items[0].status.podIP}' 2>/dev/null)

if [ -z "$IP_AUTECO" ] || [ -z "$IP_PLATAFORMAS" ]; then
  echo "ERROR: No se pudieron obtener los IPs. Verifica kubectl."
  exit 1
fi

echo "  nfs-server-auteco   → $IP_AUTECO"
echo "  nfs-server-pl       → $IP_PLATAFORMAS"
echo ""

echo "=== Actualizando script en el bastion ==="
ssh $SSH_OPTS -i "$KEY" $BASTION \
  "sudo sed -i 's|^NFS_AUTECO=.*|NFS_AUTECO=\"$IP_AUTECO\"|' /usr/local/bin/remount-efs.sh && \
   sudo sed -i 's|^NFS_PLATAFORMAS=.*|NFS_PLATAFORMAS=\"$IP_PLATAFORMAS\"|' /usr/local/bin/remount-efs.sh && \
   echo 'IPs actualizados en el script'"

echo ""
echo "=== Ejecutando remontaje ==="
ssh $SSH_OPTS -i "$KEY" $BASTION "sudo /usr/local/bin/remount-efs.sh"
