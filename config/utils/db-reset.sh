#!/bin/bash
# ============================================
# MusicAll - Database Reset Script
# Resetta il database e lo ricrea da zero
# ============================================

# Configurazione
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DB_FILE="$PROJECT_ROOT/database/musicall.sqlite"
SCHEMA_FILE="$PROJECT_ROOT/database/schema_sqlite.sql"
PHP_BIN="/c/portable/php-8.4.5/php.exe"

# Colori per output
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
echo "  MusicAll - Database Reset"
echo "============================================"
echo ""

# Verifica esistenza schema
if [ ! -f "$SCHEMA_FILE" ]; then
    log_error "Schema non trovato: $SCHEMA_FILE"
    exit 1
fi

# Backup automatico se il database esiste
if [ -f "$DB_FILE" ]; then
    log_warning "Database esistente trovato!"
    echo ""
    read -p "Vuoi creare un backup prima di resettare? (Y/n): " -n 1 -r
    echo ""
    
    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        log_info "Creazione backup automatico..."
        bash "$SCRIPT_DIR/db-backup.sh"
        echo ""
    fi
    
    log_info "Eliminazione database esistente..."
    rm "$DB_FILE"
    log_success "Database eliminato"
fi

# Crea nuovo database vuoto
log_info "Creazione nuovo database..."
touch "$DB_FILE"

# Importa schema
log_info "Importazione schema SQLite..."
cat "$SCHEMA_FILE" | "$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents("php://stdin");
try {
    $db->exec($sql);
    echo "✅ Schema importato con successo!\n";
    
    // Verifica tabelle create
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type=\"table\" ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "\nTabelle create: " . count($tables) . "\n";
    foreach($tables as $table) {
        $count = $db->query("SELECT COUNT(*) FROM $table")->fetchColumn();
        echo "  ✓ $table ($count record)\n";
    }
} catch (Exception $e) {
    echo "❌ Errore: " . $e->getMessage() . "\n";
    exit(1);
}
' "$DB_FILE"

if [ $? -eq 0 ]; then
    echo ""
    log_success "Database resettato con successo!"
    echo ""
    log_info "Prossimi passi:"
    echo "  1. Importa docenti:  bash config/utils/db-import-docenti.sh"
    echo "  2. Importa soci:  bash config/utils/db-import-soci.sh"
    echo "  3. Import completo:  bash config/utils/db-import-all.sh"
else
    log_error "Errore durante il reset del database"
    exit 1
fi

echo ""
echo "============================================"