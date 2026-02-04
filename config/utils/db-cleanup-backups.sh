#!/bin/bash
# ============================================
# MusicAll - Cleanup Backups Script
# Pulizia backup vecchi (mantiene ultimi N)
# ============================================

# Configurazione
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
BACKUP_DIR="$PROJECT_ROOT/database/backups"
KEEP_LAST=10  # Numero backup da mantenere

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
echo "  MusicAll - Cleanup Backups"
echo "============================================"
echo ""

# Verifica directory backup
if [ ! -d "$BACKUP_DIR" ]; then
    log_error "Directory backup non trovata: $BACKUP_DIR"
    exit 1
fi

# Conta backup esistenti
TOTAL_BACKUPS=$(ls -1 "$BACKUP_DIR"/*.sqlite 2>/dev/null | wc -l)

if [ $TOTAL_BACKUPS -eq 0 ]; then
    log_info "Nessun backup trovato"
    exit 0
fi

log_info "Backup totali: $TOTAL_BACKUPS"
log_info "Backup da mantenere: $KEEP_LAST"
echo ""

if [ $TOTAL_BACKUPS -le $KEEP_LAST ]; then
    log_success "Numero backup OK, nessuna pulizia necessaria"
    exit 0
fi

# Calcola backup da eliminare
TO_DELETE=$((TOTAL_BACKUPS - KEEP_LAST))
log_warning "Backup da eliminare: $TO_DELETE"
echo ""

# Lista backup da eliminare
log_info "File che verranno eliminati:"
ls -1t "$BACKUP_DIR"/*.sqlite | tail -n +$((KEEP_LAST + 1)) | while read file; do
    filename=$(basename "$file")
    size=$(du -h "$file" | cut -f1)
    echo "  - $filename ($size)"
done

echo ""
read -p "Confermi l'eliminazione? (y/N): " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    log_info "Operazione annullata"
    exit 0
fi

# Elimina backup vecchi
log_info "Eliminazione in corso..."
DELETED=0
ls -1t "$BACKUP_DIR"/*.sqlite | tail -n +$((KEEP_LAST + 1)) | while read file; do
    rm "$file"
    if [ $? -eq 0 ]; then
        DELETED=$((DELETED + 1))
        echo "  ✓ $(basename "$file")"
    fi
done

echo ""
log_success "Pulizia completata!"
log_info "Backup rimanenti: $KEEP_LAST"

# Calcola spazio liberato
TOTAL_SIZE=$(du -sh "$BACKUP_DIR" | cut -f1)
log_info "Spazio totale backup: $TOTAL_SIZE"

echo ""
echo "============================================"