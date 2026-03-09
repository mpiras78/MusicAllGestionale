#!/bin/bash
# ============================================
# MusicAll - Import Completo Database
# Resetta DB e importa: Schema + Docenti + Soci
# ============================================

# Configurazione
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"
DB_FILE="$PROJECT_ROOT/database/musicall.sqlite"
SCHEMA_FILE="$PROJECT_ROOT/database/schema_sqlite.sql"
DOCENTI_FILE="$PROJECT_ROOT/database/insert_docenti.sql"
SOCI_FILE="$PROJECT_ROOT/database/insert_soci.sql"
PHP_BIN="/c/portable/php-8.4.5/php.exe"

# Colori
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

log_info() { echo -e "${BLUE}[INFO]${NC} $1"; }
log_success() { echo -e "${GREEN}[SUCCESS]${NC} $1"; }
log_warning() { echo -e "${YELLOW}[WARNING]${NC} $1"; }
log_error() { echo -e "${RED}[ERROR]${NC} $1"; }
log_step() { echo -e "${CYAN}[STEP $1/4]${NC} $2"; }

# Header
clear
echo "╔════════════════════════════════════════════╗"
echo "║   MusicAll - Import Completo Database     ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Verifica file necessari
log_info "Verifica file necessari..."
MISSING=0

if [ ! -f "$SCHEMA_FILE" ]; then
    log_error "Schema non trovato: $SCHEMA_FILE"
    MISSING=1
fi

if [ ! -f "$DOCENTI_FILE" ]; then
    log_error "Script docenti non trovato: $DOCENTI_FILE"
    MISSING=1
fi

if [ ! -f "$SOCI_FILE" ]; then
    log_error "Script soci non trovato: $SOCI_FILE"
    MISSING=1
fi

if [ $MISSING -eq 1 ]; then
    exit 1
fi

log_success "Tutti i file necessari trovati"
echo ""

# ====================================
# STEP 1: Backup
# ====================================
log_step 1 "Backup database esistente"
if [ -f "$DB_FILE" ]; then
    bash "$SCRIPT_DIR/db-backup.sh"
    echo ""
else
    log_info "Nessun database esistente da backuppare"
    echo ""
fi

# ====================================
# STEP 2: Reset & Schema
# ====================================
log_step 2 "Reset database e import schema"
if [ -f "$DB_FILE" ]; then
    rm "$DB_FILE"
fi
touch "$DB_FILE"

cat "$SCHEMA_FILE" | "$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents("php://stdin");
try {
    $db->exec($sql);
    echo "✅ Schema importato\n";
} catch (Exception $e) {
    echo "❌ Errore schema: " . $e->getMessage() . "\n";
    exit(1);
}
' "$DB_FILE"

if [ $? -ne 0 ]; then
    log_error "Errore durante import schema"
    exit 1
fi
echo ""

# ====================================
# STEP 3: Import Docenti
# ====================================
log_step 3 "Import docenti e relazioni materie"
cat "$DOCENTI_FILE" | "$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents("php://stdin");
try {
    $db->exec($sql);
    $docenti = $db->query("SELECT COUNT(*) FROM docenti")->fetchColumn();
    $relazioni = $db->query("SELECT COUNT(*) FROM docenti_materie")->fetchColumn();
    echo "✅ $docenti docenti importati\n";
    echo "✅ $relazioni relazioni docenti-materie create\n";
} catch (Exception $e) {
    echo "❌ Errore docenti: " . $e->getMessage() . "\n";
    exit(1);
}
' "$DB_FILE"

if [ $? -ne 0 ]; then
    log_error "Errore durante import docenti"
    exit 1
fi
echo ""

# ====================================
# STEP 4: Import Soci
# ====================================
log_step 4 "Import soci"
cat "$SOCI_FILE" | "$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents("php://stdin");
try {
    $db->exec($sql);
    $soci = $db->query("SELECT COUNT(*) FROM soci")->fetchColumn();
    echo "✅ $soci soci importati\n";
} catch (Exception $e) {
    echo "❌ Errore soci: " . $e->getMessage() . "\n";
    exit(1);
}
' "$DB_FILE"

if [ $? -ne 0 ]; then
    log_error "Errore durante import soci"
    exit 1
fi
echo ""

# ====================================
# STEP 5: Import Lezioni
# ====================================
LEZIONI_FILE="$PROJECT_ROOT/database/insert_lezioni.sql"
if [ -f "$LEZIONI_FILE" ]; then
    log_step 5 "Import lezioni (associazioni soci-docenti-materie)"
    cat "$LEZIONI_FILE" | "$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$sql = file_get_contents("php://stdin");
try {
    $db->exec($sql);
    $lezioni = $db->query("SELECT COUNT(*) FROM lezioni")->fetchColumn();
    $laboratori = $db->query("SELECT COUNT(*) FROM lezioni WHERE tipo=\"laboratorio\"")->fetchColumn();
    echo "✅ $lezioni lezioni importate\n";
    echo "✅ $laboratori laboratori inclusi\n";
} catch (Exception $e) {
    echo "❌ Errore lezioni: " . $e->getMessage() . "\n";
    exit(1);
}
' "$DB_FILE"

    if [ $? -ne 0 ]; then
        log_error "Errore durante import lezioni"
        exit 1
    fi
    echo ""
else
    log_warning "File lezioni non trovato, saltato"
    echo ""
fi

# ====================================
# Riepilogo Finale
# ====================================
echo "╔════════════════════════════════════════════╗"
echo "║          RIEPILOGO IMPORT COMPLETO         ║"
echo "╚════════════════════════════════════════════╝"
echo ""

"$PHP_BIN" -r '
$db = new PDO("sqlite:" . $argv[1]);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stats = [
    "users" => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    "docenti" => $db->query("SELECT COUNT(*) FROM docenti")->fetchColumn(),
    "docenti_materie" => $db->query("SELECT COUNT(*) FROM docenti_materie")->fetchColumn(),
    "soci" => $db->query("SELECT COUNT(*) FROM soci")->fetchColumn(),
    "aule" => $db->query("SELECT COUNT(*) FROM aule")->fetchColumn(),
    "materie" => $db->query("SELECT COUNT(*) FROM materie")->fetchColumn(),
    "slot_orari" => $db->query("SELECT COUNT(*) FROM slot_orari")->fetchColumn(),
];

echo "📊 Statistiche Database:\n\n";
echo "  👥 Utenti:              " . $stats["users"] . " (admin pronto)\n";
echo "  👨‍🏫 Docenti:             " . $stats["docenti"] . "\n";
echo "  🔗 Relazioni D-M:       " . $stats["docenti_materie"] . "\n";
echo "  🎓 Soci:             " . $stats["soci"] . "\n";
echo "  🏫 Aule:                " . $stats["aule"] . "\n";
echo "  📚 Materie:             " . $stats["materie"] . "\n";
echo "  ⏰ Slot Orari:          " . $stats["slot_orari"] . "\n";

$size = filesize($argv[1]);
$sizeKB = round($size / 1024, 2);
echo "\n  💾 Dimensione DB:       " . $sizeKB . " KB\n";
' "$DB_FILE"

echo ""
echo "╔════════════════════════════════════════════╗"
log_success "IMPORT COMPLETATO CON SUCCESSO!"
echo "╚════════════════════════════════════════════╝"
echo ""
log_info "Credenziali Admin:"
echo "  Username: admin"
echo "  Password: admin123"
echo ""
log_info "Avvia server: php -S localhost:8000"
echo ""