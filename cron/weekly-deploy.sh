#!/bin/bash
# Cron semanal de deploy automático — Hostinger
# Cadastrar como "Personalizado": /bin/bash /home/u567708141/salao-beleza/cron/weekly-deploy.sh
# Schedule: 0 3 * * 0  (todo domingo às 03:00)

set -e

LARAVEL="/home/u567708141/salao-beleza"
LOG="$LARAVEL/storage/logs/deploy-cron.log"

echo "========================================" >> "$LOG"
echo "[$(date '+%Y-%m-%d %H:%M:%S')] Iniciando deploy semanal" >> "$LOG"

cd "$LARAVEL"

echo "[$(date '+%Y-%m-%d %H:%M:%S')] git pull..." >> "$LOG"
git pull origin main >> "$LOG" 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Rodando deploy-hostinger.sh..." >> "$LOG"
bash "$LARAVEL/deploy-hostinger.sh" >> "$LOG" 2>&1

echo "[$(date '+%Y-%m-%d %H:%M:%S')] Deploy concluído com sucesso." >> "$LOG"
