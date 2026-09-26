# 🔑 Password Reset System - MusicAll v3.0

## 📋 Riepilogo Feature

Abbiamo implementato un **sistema completo di reset password** con:

### ✅ Componenti Implementati

1. **`set-password.php`** - Script CLI per impostare/resettare password admin
   - Uso: `php set-password.php [password]`
   - Crea automaticamente tabella users se non esiste
   - Aggiunge colonne per token reset
   - Default: admin123

2. **`forgot-password.php`** - Pagina principale di password reset
   - ✅ STEP 1: Form per richiesta reset (email)
   - ✅ STEP 2: Form per nuovo password con token
   - ✅ Token generation (32 bytes random hex)
   - ✅ Token expiry: 1 ora (3600 secondi)
   - ✅ Activity logging (sec-009)
   - ✅ Password validation (sec-005)
   - ✅ Mock email display in dev mode

3. **`login.php`** - Aggiunto link "Hai dimenticato la password?"
   - Redirect a forgot-password.php

4. **`SET_PASSWORD.bat`** - Batch file Windows per eseguire lo script

---

## 🗂️ Database Schema

La tabella `users` avrà questi nuovi campi:

```sql
-- Aggiunti automaticamente da set-password.php:
password_reset_token VARCHAR(255)     -- Token reset (64 char hex)
password_reset_expires DATETIME       -- Scadenza token (now + 1 hour)
```

---

## 🚀 Come Usare - Setup Locale

### 1️⃣ Imposta Password Admin

```bash
# Comando base (password default: admin123)
php set-password.php

# Con password personalizzata
php set-password.php MySecurePass123!

# Batch file Windows
SET_PASSWORD.bat MySecurePass123!
```

**Output Atteso:**
```
╔═══════════════════════════════════════════════╗
║     🔑 MUSICALL - Set Admin Password          ║
╚═══════════════════════════════════════════════╝

Processing:
  Username: admin
  Password: ****************************
  Hash: $2y$10$oK8...

✅ Admin user created
✅ Verification:
  Username: admin
  Email: admin@musicall.it
  Role: admin

🌐 Login at: http://localhost:8000/login.php
👤 Username: admin
🔑 Password: admin123
```

### 2️⃣ Testa Login

```
http://localhost:8000/login.php
Username: admin
Password: [quello che hai usato]
```

### 3️⃣ Testa Password Reset Flow

**Via UI:**
1. Vai a: `http://localhost:8000/forgot-password.php`
2. Step 1: Inserisci email admin@musicall.it
3. Ricevi link reset (mock in dev mode)
4. Clicca link reset
5. Step 2: Inserisci nuova password
6. Verifica: Nuovo login con nuova password

**In Debug Mode:**
```
Se DEBUG_MODE=true in config.php, vedrai il link reset direttamente:
[Link reset] https://localhost/reset-password.php?token=abc123...
```

---

## 🔒 Sicurezza - Standards Implemented

### Rate Limiting (sec-001)
- Login: max 5 tentativi / 15 min per IP ✅
- Forgot Password: max 1 email / 10 min per IP (TODO)

### Password Validation (sec-005)
- Min 12 caratteri ✅
- Richiede: MAIUSCOLE + minuscole + numeri + simboli ✅
- Validazione in reset form ✅

### Token Security
- **Algoritmo**: 32 bytes random hex (256-bit entropy) ✅
- **Lunghezza**: 64 char (32 bytes * 2 hex)
- **Scadenza**: 1 ora (3600 secondi) ✅
- **Storage**: Database, colonna password_reset_token ✅
- **Uso**: Via URL parameter (secure su HTTPS) ✅

### Activity Logging (sec-009)
- ✅ password_reset_request → activity_log
- ✅ password_reset_complete → activity_log
- Registra: IP, timestamp, user_id

### CSRF Protection
- Form forgot-password.php usa `CSRFHelper::field()`
- Validazione CSRF nel POST (se implementato in bootstrap)

### Email (TODO - Da Configurare)
```php
// Placeholder per email futura
// TODO: Integrare SMTP oppure SendGrid
// $mailService->send($email, 'Password Reset', $resetUrl);

// In dev mode: mostra link direttamente
if (DEBUG_MODE) {
    echo "Link reset: $resetUrl";
}
```

---

## 📊 Activity Logging Examples

```sql
-- Password reset richiesto
INSERT INTO activity_log (...) VALUES (
    user_id=5, 
    action='password_reset_request',
    description='Password reset richiesto',
    ip_address='192.168.1.100'
);

-- Password reset completato
INSERT INTO activity_log (...) VALUES (
    user_id=5, 
    action='password_reset_complete',
    description='Password resetata',
    ip_address='192.168.1.100'
);
```

---

## 🔧 Configuration

### config.php

```php
// Questi valori sono già configurati:
PASSWORD_MIN_LENGTH = 12                    // sec-005
PASSWORD_REQUIRE_UPPERCASE = true           // sec-005
PASSWORD_REQUIRE_LOWERCASE = true           // sec-005
PASSWORD_REQUIRE_NUMBERS = true             // sec-005
PASSWORD_REQUIRE_SYMBOLS = true             // sec-005
```

### reset-password.php (Token Parameters)

```php
// Token URL format:
http://localhost:8000/forgot-password.php?token=ABC123DEF456...

// Token validation:
// - Verifica colonna password_reset_expires > NOW()
// - Verifica colonna password_reset_token = provided_token
// - Se OK: mostra form password reset
// - Se scaduto/invalido: mostra errore + link nuovo reset
```

---

## 📝 File Changes Summary

### ✅ Created Files:
- `set-password.php` (CLI utility)
- `forgot-password.php` (Main reset page)
- `SET_PASSWORD.bat` (Batch wrapper)

### ✅ Modified Files:
- `login.php` - Aggiunto link "Forgot Password"

### 📚 Database Changes:
- Colonne aggiunte automaticamente da `set-password.php`:
  - `password_reset_token VARCHAR(255)`
  - `password_reset_expires DATETIME`

---

## ⚠️ Known Limitations / TODO

1. **Email Service** - Placeholder only
   - [ ] Implementare SMTP configuration
   - [ ] Configurare SendGrid API oppure mailgun
   - [ ] Template email HTML

2. **Rate Limiting su Forgot** - Non implementato
   - [ ] Max 1 email / 10 min per IP
   - [ ] Implementare RateLimiter per forgot-password.php

3. **Token Cleanup** - Non implementato
   - [ ] Cron job per pulire token scaduti
   - [ ] `DELETE FROM users WHERE password_reset_expires < NOW()`

4. **Security: X-Forwarded-For**
   - [ ] Rate limiter dovrebbe considerare proxy
   - [ ] Attualmente usa $_SERVER['REMOTE_ADDR']

---

## 🧪 Test Cases

### Test 1: Imposta Password Admin
```
php set-password.php MyNewPass123!
Expected: User admin creato/aggiornato, hash salvato
```

### Test 2: Login Con Nuova Password
```
URL: http://localhost:8000/login.php
Username: admin
Password: MyNewPass123!
Expected: Login riuscito, redirect index.php
```

### Test 3: Forgot Password Flow (Dev Mode)
```
1. Clicca link "Hai dimenticato la password?"
2. Inserisci: admin@musicall.it
3. Vedi: Link reset (mock in dev)
4. Clicca link reset
5. Inserisci nuova password: AnotherPass123!
6. Vedi: Success message
7. Redirect login.php
Expected: Nuovo login con AnotherPass123! funziona
```

### Test 4: Token Expiry
```
1. Genera link reset (token A, scadenza +1h)
2. Attendi... (in test: modifica DATETIME +2h in DB)
3. Clicca link reset (token A scaduto)
Expected: Errore "Token non valido o scaduto"
         Link "Richiedi un nuovo reset"
```

### Test 5: Token Tampering
```
1. Genera link reset: ...?token=ABC123
2. Modifica URL: ...?token=XYZ999
3. Clicca
Expected: Errore "Token non valido o scaduto"
```

---

## 🔄 Integration Checklist

- [x] set-password.php creato
- [x] forgot-password.php creato
- [x] login.php aggiornato (link forgot password)
- [x] Database schema updated (password_reset_* fields)
- [x] Password validation sec-005 integrata
- [x] Activity logging sec-009 integrata
- [x] Token generation (32 bytes random hex)
- [ ] Email service configurato
- [ ] Rate limiting forgot-password implementato
- [ ] Token cleanup cron job implementato
- [ ] X-Forwarded-For proxy support

---

## 📈 Progress Tracking

**Categoria**: CODE_QUALITY (Fase 9 - Polish UI v3.0)  
**Tasks Completati**:
- ✅ feat-profile-password (Cambio password in profilo)
- ✅ feat-password-reset (Sistema reset con token)
- ✅ feat-set-password-cli (CLI utility)

**Progress**: 3/5 Code Quality (60%)

---

## 🎯 Next Steps

1. **Immediato**: Testare set-password.php per impostare admin
2. **Breve**: Implementare email service (SMTP o SendGrid)
3. **Medio**: Rate limiting forgot-password.php
4. **Lungo**: Cron job cleanup token scaduti

---

**Documento Generato**: 2026-07-26 18:30  
**Versione MusicAll**: v3.0  
**Fase**: Secure Setup + Password Reset System  
**Status**: ✅ Ready for Testing
