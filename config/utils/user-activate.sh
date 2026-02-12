#!/bin/bash

##############################################
# MusicAll - Attivazione Utente
##############################################
# Genera token di attivazione per un utente
# e mostra il link di attivazione
##############################################

# Colori output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Configurazione
PHP_BIN="/c/portable/php-8.4.5/php.exe"
PROJECT_ROOT="c:/git/ct-poc/MusicAll"
DB_PATH="$PROJECT_ROOT/database/musicall.sqlite"
DEFAULT_HOST="localhost"
DEFAULT_PORT="8000"

# Banner
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║     MusicAll - Attivazione Utente         ║"
echo "╚════════════════════════════════════════════╝"
echo ""

# Verifica PHP
if [ ! -f "$PHP_BIN" ]; then
    echo -e "${RED}[ERROR]${NC} PHP non trovato in: $PHP_BIN"
    exit 1
fi

# Verifica database
if [ ! -f "$DB_PATH" ]; then
    echo -e "${RED}[ERROR]${NC} Database non trovato: $DB_PATH"
    echo -e "${YELLOW}[INFO]${NC} Esegui prima: bash config/utils/db-import-all.sh"
    exit 1
fi

# Input username
if [ -z "$1" ]; then
    echo -e "${BLUE}[INFO]${NC} Inserisci lo username dell'utente da attivare"
    echo ""
    read -p "Username: " USERNAME
else
    USERNAME="$1"
fi

if [ -z "$USERNAME" ]; then
    echo -e "${RED}[ERROR]${NC} Username obbligatorio!"
    exit 1
fi

# Host e porta configurabili
HOST=${2:-$DEFAULT_HOST}
PORT=${3:-$DEFAULT_PORT}

echo ""
echo -e "${BLUE}[INFO]${NC} Ricerca utente: ${GREEN}$USERNAME${NC}"
echo ""

# Crea script PHP temporaneo per gestire l'attivazione
TMP_SCRIPT=$(mktemp --suffix=.php)
cat > "$TMP_SCRIPT" << 'PHPSCRIPT'
<?php
require_once __DIR__ . '/includes/bootstrap.php';

$username = $argv[1] ?? '';
if (empty($username)) {
    echo json_encode(['success' => false, 'error' => 'Username mancante']);
    exit(1);
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Cerca utente
    $stmt = $db->prepare("SELECT id, username, email, attivo FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
        exit(1);
    }
    
    // Verifica se già attivo
    if ($user['attivo'] == 1) {
        echo json_encode([
            'success' => false, 
            'error' => 'Utente già attivo',
            'user' => $user
        ]);
        exit(0);
    }
    
    // Genera token di attivazione
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Salva token
    $stmt = $db->prepare("
        INSERT INTO activation_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, ?, datetime('now'))
    ");
    $stmt->execute([$user['id'], $token, $expires]);
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'token' => $token,
        'expires' => $expires
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
PHPSCRIPT

# Esegui script PHP
cd "$PROJECT_ROOT" || exit 1
RESULT=$("$PHP_BIN" "$TMP_SCRIPT" "$USERNAME" 2>&1)
EXIT_CODE=$?

# Cleanup script temporaneo
rm -f "$TMP_SCRIPT"

# Parsing risultato JSON
SUCCESS=$(echo "$RESULT" | grep -o '"success":[^,}]*' | cut -d':' -f2 | tr -d ' "')
ERROR=$(echo "$RESULT" | grep -o '"error":"[^"]*"' | cut -d'"' -f4)
TOKEN=$(echo "$RESULT" | grep -o '"token":"[^"]*"' | cut -d'"' -f4)
EMAIL=$(echo "$RESULT" | grep -o '"email":"[^"]*"' | cut -d'"' -f4)
USER_ID=$(echo "$RESULT" | grep -o '"id":[0-9]*' | cut -d':' -f2)
EXPIRES=$(echo "$RESULT" | grep -o '"expires":"[^"]*"' | cut -d'"' -f4)
ATTIVO=$(echo "$RESULT" | grep -o '"attivo":[0-9]*' | cut -d':' -f2)

# Gestione errori
if [ "$SUCCESS" != "true" ]; then
    echo -e "${RED}[ERROR]${NC} $ERROR"
    
    # Se utente già attivo, mostra info
    if [ "$ATTIVO" == "1" ]; then
        echo ""
        echo -e "${YELLOW}[INFO]${NC} L'utente è già attivo e può fare login:"
        echo ""
        echo "  👤 Username: ${GREEN}$USERNAME${NC}"
        echo "  📧 Email: ${CYAN}$EMAIL${NC}"
        echo "  🔗 URL Login: ${BLUE}http://${HOST}:${PORT}/login.php${NC}"
        echo ""
    fi
    exit 1
fi

# Successo - Mostra informazioni
echo -e "${GREEN}[SUCCESS]${NC} Token di attivazione generato!"
echo ""
echo "════════════════════════════════════════════"
echo -e "${BLUE}📋 INFORMAZIONI UTENTE${NC}"
echo "════════════════════════════════════════════"
echo -e "  👤 Username:    ${GREEN}$USERNAME${NC}"
echo -e "  📧 Email:       ${CYAN}$EMAIL${NC}"
echo -e "  🆔 User ID:     ${YELLOW}$USER_ID${NC}"
echo -e "  🔑 Token:       ${YELLOW}${TOKEN:0:20}...${NC}"
echo -e "  ⏰ Scade:       ${YELLOW}$EXPIRES${NC}"
echo ""
echo "════════════════════════════════════════════"
echo -e "${BLUE}🔗 LINK DI ATTIVAZIONE${NC}"
echo "════════════════════════════════════════════"
echo ""
echo -e "${GREEN}http://${HOST}:${PORT}/activate.php?token=${TOKEN}${NC}"
echo ""
echo "════════════════════════════════════════════"
echo ""
echo -e "${YELLOW}[INFO]${NC} Invia questo link all'utente per completare l'attivazione"
echo -e "${YELLOW}[INFO]${NC} Il link è valido per 24 ore"
echo ""
echo -e "${BLUE}[NEXT STEPS]${NC}"
echo "  1. Copia il link sopra"
echo "  2. Invialo via email all'utente"
echo "  3. L'utente clicca il link e imposta la password"
echo "  4. L'utente può fare login"
echo ""