#!/bin/bash

##############################################
# MusicAll - Stop Applicativo
##############################################
# Ferma il server PHP built-in
# Cerca e termina processi PHP sulla porta 8000
##############################################

# Colori output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurazione
PROJECT_ROOT="c:/git/ct-poc/MusicAll"
DEFAULT_PORT=8000

# Banner
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║      MusicAll - Stop Applicativo          ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Porta configurabile
PORT=${1:-$DEFAULT_PORT}

echo -e "${BLUE}[INFO]${NC} Ricerca processi PHP sulla porta $PORT..."
echo ""

# Trova processi sulla porta specificata
PROCESSES=$(netstat -ano | grep ":$PORT " | grep "LISTENING" | awk '{print $5}')

if [ -z "$PROCESSES" ]; then
    echo -e "${YELLOW}[INFO]${NC} Nessun processo trovato sulla porta $PORT"
    echo -e "${BLUE}[INFO]${NC} Il server potrebbe essere già spento."
    exit 0
fi

# Mostra processi trovati
echo -e "${BLUE}[INFO]${NC} Processi trovati sulla porta $PORT:"
echo ""
netstat -ano | grep ":$PORT " | grep "LISTENING"
echo ""

# Estrai PID univoci
PIDS=$(echo "$PROCESSES" | sort -u)

echo -e "${YELLOW}[WARNING]${NC} PID da terminare:"
for pid in $PIDS; do
    # Verifica se è davvero un processo PHP
    PROCESS_NAME=$(tasklist //FI "PID eq $pid" //FO CSV //NH 2>/dev/null | cut -d',' -f1 | tr -d '"')
    echo -e "  • PID ${GREEN}$pid${NC} - $PROCESS_NAME"
done
echo ""

# Conferma
read -p "Confermi la terminazione? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}[INFO]${NC} Operazione annullata."
    exit 0
fi

# Termina i processi
echo -e "${BLUE}[INFO]${NC} Terminazione processi in corso..."
echo ""

SUCCESS_COUNT=0
FAIL_COUNT=0

for pid in $PIDS; do
    if taskkill //PID $pid //F > /dev/null 2>&1; then
        echo -e "${GREEN}[SUCCESS]${NC} PID $pid terminato"
        SUCCESS_COUNT=$((SUCCESS_COUNT + 1))
    else
        echo -e "${RED}[ERROR]${NC} Impossibile terminare PID $pid"
        FAIL_COUNT=$((FAIL_COUNT + 1))
    fi
done

echo ""
echo "════════════════════════════════════════════"
echo -e "${BLUE}[RIEPILOGO]${NC}"
echo "  ✓ Processi terminati: ${GREEN}$SUCCESS_COUNT${NC}"
if [ $FAIL_COUNT -gt 0 ]; then
    echo "  ✗ Processi falliti: ${RED}$FAIL_COUNT${NC}"
fi
echo "════════════════════════════════════════════"

# Cleanup file PID se esiste
if [ -f "$PROJECT_ROOT/.app.pid" ]; then
    rm -f "$PROJECT_ROOT/.app.pid"
    echo -e "${BLUE}[INFO]${NC} Cleanup file PID completato"
fi

echo ""
if [ $FAIL_COUNT -eq 0 ]; then
    echo -e "${GREEN}[SUCCESS]${NC} Server fermato con successo!"
else
    echo -e "${YELLOW}[WARNING]${NC} Alcuni processi non sono stati terminati."
    echo -e "${YELLOW}[INFO]${NC} Prova a chiudere manualmente il terminale del server."
fi
echo ""