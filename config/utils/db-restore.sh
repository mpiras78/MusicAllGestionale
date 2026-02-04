#!/bin/bash
# ============================================
# MusicAll - Database Restore Script
# Ripristina un backup del database
# ============================================

# Configurazione
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DB_FILE="$PROJECT_ROOT/database/musicall.sqlite"
BACKUP_DIR="$PROJECT_ROOT/database/backups"

# Colori
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }

# Header
echo "============================================"
echo "  MusicAll - Database Restore"
echo "============================================"
echo ""

# Verifica directory backup
if [ ! -d "$BACKUP_DIR" ]; then
    log_error "Directory backup non trovata: $BACKUP_DIR"
    exit 1
fi

# Lista backup disponibili
BACKUPS=($(ls -1t "$BACKUP_DIR"/*.sqlite 2>/dev/null))

if [ ${#BACKUPS[@]} -eq 0 ]; then
    log_error "Nessun backup disponibile in $BACKUP_DIR"
    exit 1
fi

# Mostra backup disponibili
log_info "Backup disponibili:"
echo ""
for i in "${!BACKUPS[@]}"; do
    filename=$(basename "${BACKUPS[$i]}")
    size=$(du -h "${BACKUPS[$i]}" | cut -f1)
    date=$(stat -c %y "${BACKUPS[$i]}" 2>/dev/null || stat -f "%Sm" "${BACKUPS[$i]}")
    echo "  [$((i+1))] $filename ($size) - $date"
done

echo ""
read -p "Seleziona il backup da ripristinare [1-${#BACKUPS[@]}]: " selection

# Valida selezione
if ! [[ "$selection" =~ ^[0-9]+$ ]] || [ "$selection" -lt 1 ] || [ "$selection" -gt ${#BACKUPS[@]} ]; then
    log_error "Selezione non valida"
    exit 1
fi

SELECTED_BACKUP="${BACKUPS[$((selection-1))]}"
log_info "Backup selezionato: $(basename "$SELECTED_BACKUP")"
echo ""

# Conferma
log_warning "ATTENZIONE: Il database corrente verrà SOVRASCRITTO!"
read -p "Vuoi continuare? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log_info "Operazione annullata"
    exit 0
fi

# Backup del database corrente prima del restore
if [ -f "$DB_FILE" ]; then
    log_info "Backup database corrente prima del restore..."
    bash "$SCRIPT_DIR/db-backup.sh"
    echo ""
fi

# Ripristina backup
log_info "Ripristino in corso..."
cp "$SELECTED_BACKUP" "$DB_FILE"

if [ $? -eq 0 ]; then
    log_success "Database ripristinato con successo!"
    
    # Verifica integrità
    SIZE=$(du -h "$DB_FILE" | cut -f1)
    log_info "Dimensione database ripristinato: $SIZE"
else
    log_error "Errore durante il ripristino"
    exit 1
fi

echo ""
echo "============================================"