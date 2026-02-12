#!/bin/bash

##############################################
# MusicAll - Reset Password Utente
##############################################
# Reimposta la password di un utente
# Utile per recupero accesso
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

# Banner
echo ""
echo "╔════════════════════════════════════════════╗"
echo "║    MusicAll - Reset Password Utente       ║"
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
    echo -e "${BLUE}[INFO]${NC} Inserisci lo username dell'utente"
    echo ""
    read -p "Username: " USERNAME
else
    USERNAME="$1"
fi

if [ -z "$USERNAME" ]; then
    echo -e "${RED}[ERROR]${NC} Username obbligatorio!"
    exit 1
fi

# Input nuova password
if [ -z "$2" ]; then
    echo ""
    read -sp "Nuova password: " NEW_PASSWORD
    echo ""
    read -sp "Conferma password: " CONFIRM_PASSWORD
    echo ""
    
    if [ "$NEW_PASSWORD" != "$CONFIRM_PASSWORD" ]; then
        echo -e "${RED}[ERROR]${NC} Le password non coincidono!"
        exit 1
    fi
else
    NEW_PASSWORD="$2"
fi

if [ -z "$NEW_PASSWORD" ]; then
    echo -e "${RED}[ERROR]${NC} Password obbligatoria!"
    exit 1
fi

# Verifica lunghezza password
if [ ${#NEW_PASSWORD} -lt 6 ]; then
    echo -e "${RED}[ERROR]${NC} La password deve essere di almeno 6 caratteri!"
    exit 1
fi

echo ""
echo -e "${BLUE}[INFO]${NC} Reset password per utente: ${GREEN}$USERNAME${NC}"
echo ""

# Crea script PHP temporaneo per reset password
TMP_SCRIPT=$(mktemp --suffix=.php)
cat > "$TMP_SCRIPT" << 'PHPSCRIPT'
<?php
require_once __DIR__ . '/includes/bootstrap.php';

$username = $argv[1] ?? '';
$newPassword = $argv[2] ?? '';

if (empty($username) || empty($newPassword)) {
    echo json_encode(['success' => false, 'error' => 'Parametri mancanti']);
    exit(1);
}

try {
    $db = Database::getInstance()->getConnection();
    
    // Cerca utente
    $stmt = $db->prepare("SELECT id, username, email, ruolo, attivo FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'error' => 'Utente non trovato']);
        exit(1);
    }
    
    // Hash password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Aggiorna password
    $stmt = $db->prepare("UPDATE users SET password = ?, updated_at = datetime('now') WHERE id = ?");
    $stmt->execute([$hashedPassword, $user['id']]);
    
    // Se utente non attivo, attivalo
    if ($user['attivo'] == 0) {
        $stmt = $db->prepare("UPDATE users SET attivo = 1 WHERE id = ?");
        $stmt->execute([$user['id']]);
        $user['attivo'] = 1;
        $user['activated'] = true;
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit(1);
}
PHPSCRIPT

# Esegui script PHP
cd "$PROJECT_ROOT" || exit 1
RESULT=$("$PHP_BIN" "$TMP_SCRIPT" "$USERNAME" "$NEW_PASSWORD" 2>&1)
EXIT_CODE=$?

# Cleanup script temporaneo
rm -f "$TMP_SCRIPT"

# Parsing risultato JSON
SUCCESS=$(echo "$RESULT" | grep -o '"success":[^,}]*' | cut -d':' -f2 | tr -d ' "')
ERROR=$(echo "$RESULT" | grep -o '"error":"[^"]*"' | cut -d'"' -f4)
EMAIL=$(echo "$RESULT" | grep -o '"email":"[^"]*"' | cut -d'"' -f4)
RUOLO=$(echo "$RESULT" | grep -o '"ruolo":"[^"]*"' | cut -d'"' -f4)
USER_ID=$(echo "$RESULT" | grep -o '"id":[0-9]*' | cut -d':' -f2)
ATTIVO=$(echo "$RESULT" | grep -o '"attivo":[0-9]*' | cut -d':' -f2)
ACTIVATED=$(echo "$RESULT" | grep -o '"activated":true')

# Gestione errori
if [ "$SUCCESS" != "true" ]; then
    echo -e "${RED}[ERROR]${NC} $ERROR"
    exit 1
fi

# Successo
echo -e "${GREEN}[SUCCESS]${NC} Password aggiornata con successo!"
echo ""

# Mostra se utente è stato attivato
if [ ! -z "$ACTIVATED" ]; then
    echo -e "${YELLOW}[INFO]${NC} L'utente era disattivato ed è stato automaticamente attivato"
    echo ""
fi

echo "════════════════════════════════════════════"
echo -e "${BLUE}📋 INFORMAZIONI UTENTE${NC}"
echo "════════════════════════════════════════════"
echo -e "  👤 Username:    ${GREEN}$USERNAME${NC}"
echo -e "  📧 Email:       ${CYAN}$EMAIL${NC}"
echo -e "  🆔 User ID:     ${YELLOW}$USER_ID${NC}"
echo -e "  👔 Ruolo:       ${YELLOW}$RUOLO${NC}"
echo -e "  ✅ Attivo:      ${GREEN}Sì${NC}"
echo -e "  🔑 Password:    ${GREEN}Aggiornata${NC}"
echo ""
echo "════════════════════════════════════════════"
echo ""
echo -e "${BLUE}[INFO]${NC} L'utente può ora fare login con le nuove credenziali:"
echo ""
echo -e "  Username: ${GREEN}$USERNAME${NC}"
echo -e "  Password: ${GREEN}[quella appena impostata]${NC}"
echo ""