#!/bin/bash

##############################################
# MusicAll - Avvio Applicativo
##############################################
# Avvia il server PHP built-in su localhost
# Porta: 8000 (configurabile)
# Host: localhost
##############################################

# Colori output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configurazione
PHP_BIN="/c/portable/php-8.4.5/php.exe"
PROJECT_ROOT="c:/git/ct-poc/MusicAll"
DEFAULT_PORT=8000
DEFAULT_HOST="localhost"

# Banner
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║     MusicAll - Avvio Applicativo          ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Verifica PHP
if [ ! -f "$PHP_BIN" ]; then
    echo -e "${RED}[ERROR]${NC} PHP non trovato in: $PHP_BIN"
    echo -e "${YELLOW}[INFO]${NC} Controlla il percorso in config/utils/app-start.sh"
    exit 1
fi

# Porta configurabile
PORT=${1:-$DEFAULT_PORT}
HOST=${2:-$DEFAULT_HOST}

# Verifica se la porta è già in uso
echo -e "${BLUE}[INFO]${NC} Verifica porta $PORT..."
if netstat -ano | grep ":$PORT " | grep "LISTENING" > /dev/null 2>&1; then
    echo -e "${YELLOW}[WARNING]${NC} La porta $PORT è già in uso!"
    echo ""
    echo "Processi attivi sulla porta $PORT:"
    netstat -ano | grep ":$PORT " | grep "LISTENING"
    echo ""
    echo -e "${YELLOW}[AZIONE]${NC} Vuoi usare una porta diversa? Riavvia con:"
    echo "  bash config/utils/app-start.sh [PORTA]"
    echo ""
    echo "Esempio: bash config/utils/app-start.sh 8080"
    exit 1
fi

# Verifica database
DB_PATH="$PROJECT_ROOT/database/musicall.sqlite"
if [ ! -f "$DB_PATH" ]; then
    echo -e "${YELLOW}[WARNING]${NC} Database non trovato!"
    echo -e "${BLUE}[INFO]${NC} Esegui prima l'import del database:"
    echo "  bash config/utils/db-import-all.sh"
    echo ""
    read -p "Vuoi continuare comunque? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Cambia directory al progetto
cd "$PROJECT_ROOT" || exit 1

echo -e "${GREEN}[SUCCESS]${NC} PHP trovato: $(${PHP_BIN} --version | head -n 1)"
echo -e "${BLUE}[INFO]${NC} Directory progetto: $PROJECT_ROOT"
echo -e "${BLUE}[INFO]${NC} Host: $HOST"
echo -e "${BLUE}[INFO]${NC} Porta: $PORT"
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║           🚀 SERVER AVVIATO               ║"
echo "╚════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}✓${NC} URL Applicativo: ${BLUE}http://${HOST}:${PORT}${NC}"
echo ""
echo -e "${YELLOW}[INFO]${NC} Credenziali default:"
echo "  Username: ${GREEN}admin${NC}"
echo "  Password: ${GREEN}admin123${NC}"
echo ""
echo -e "${YELLOW}[INFO]${NC} Comandi utili:"
echo "  • Fermare server: ${BLUE}Ctrl+C${NC} oppure ${BLUE}bash config/utils/app-stop.sh${NC}"
echo "  • Nuovo terminale: Apri una nuova finestra Git Bash"
echo ""
echo "════════════════════════════════════════════"
echo -e "${BLUE}[LOG]${NC} Output del server (premi Ctrl+C per terminare):"
echo "════════════════════════════════════════════"
echo ""

# Salva PID per script di stop
echo $$ > "$PROJECT_ROOT/.app.pid"

# Avvia server PHP built-in
"$PHP_BIN" -S "${HOST}:${PORT}" -t "$PROJECT_ROOT"

# Cleanup al termine
rm -f "$PROJECT_ROOT/.app.pid"
echo ""
echo -e "${YELLOW}[INFO]${NC} Server terminato."