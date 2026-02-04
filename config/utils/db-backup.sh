#!/bin/bash
# ============================================
# MusicAll - Database Backup Script
# Crea backup versionati del database SQLite
# ============================================

# Configurazione
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DB_FILE="$PROJECT_ROOT/database/musicall.sqlite"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
TIMESTAMP=$(date +%Y%m%d%H%M%S)

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funzione per stampare messaggi
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Header
echo "============================================"
echo "  MusicAll - Database Backup"
echo "============================================"
echo ""

# Crea directory backup se non esiste
if [ ! -d "$BACKUP_DIR" ]; then
    log_info "Creazione directory backup..."
    mkdir -p "$BACKUP_DIR"
fi

# Verifica esistenza database
if [ ! -f "$DB_FILE" ]; then
    log_error "Database non trovato: $DB_FILE"
    exit 1
fi

# Calcola dimensione database
DB_SIZE=$(du -h "$DB_FILE" | cut -f1)
log_info "Database da backuppare: musicall.sqlite ($DB_SIZE)"

# Genera nome backup
BACKUP_FILE="$BACKUP_DIR/musicall_backup_${TIMESTAMP}.sqlite"

# Esegui backup
log_info "Creazione backup..."
cp "$DB_FILE" "$BACKUP_FILE"

if [ $? -eq 0 ]; then
    log_success "Backup creato: musicall_backup_${TIMESTAMP}.sqlite"
    
    # Verifica integrità backup
    BACKUP_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    log_info "Dimensione backup: $BACKUP_SIZE"
    
    # Lista ultimi 5 backup
    echo ""
    log_info "Ultimi 5 backup disponibili:"
    ls -lht "$BACKUP_DIR" | head -6 | tail -5 | awk '{print "  " $9 " (" $5 ")"}'
    
    # Conta totale backup
    BACKUP_COUNT=$(ls -1 "$BACKUP_DIR" | wc -l)
    echo ""
    log_info "Totale backup: $BACKUP_COUNT"
    
    # Suggerisci pulizia se troppi backup
    if [ $BACKUP_COUNT -gt 20 ]; then
        log_warning "Hai $BACKUP_COUNT backup! Considera di usare db-cleanup-backups.sh"
    fi
else
    log_error "Errore durante la creazione del backup"
    exit 1
fi

echo ""
echo "============================================"
log_success "Backup completato!"
echo "============================================"