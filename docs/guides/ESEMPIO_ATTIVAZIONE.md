# 📖 Esempio Pratico: Attivazione Utente

## 🎯 Scenario
Hai appena creato un nuovo utente nella UI e devi attivarlo (la mail non arriva in locale).

---

## 📝 Passo 1: Crea Utente nella UI

1. Apri browser: `http://localhost:8000`
2. Login come admin
3. Vai su **Gestione Utenti**
4. Click **"Nuovo Utente"**
5. Compila:
   - Username: `mario.rossi`
   - Email: `mario.rossi@test.it`
   - Password: `Test123`
   - Conferma: `Test123`
   - Ruolo: `Segreteria`
6. Click **"Salva"**

✅ **Risultato**: Alert verde "Utente creato. Email di attivazione inviata a mario.rossi@test.it"

> ⚠️ Ma la mail non arriva perché sei in localhost!

---

## 🔍 Passo 2: Recupera Token via CLI

Apri il terminale nella cartella del progetto e lancia:

```bash
php tests/get_activation_token.php mario.rossi
```

**Output Completo**:
```
=== INFORMAZIONI UTENTE ===

Username: mario.rossi
Email: mario.rossi@test.it
Account Attivo: ✗ No (da attivare)

=== TOKEN ATTIVAZIONE ===

Codice Attivazione: K4X9M2
Stato: VALIDO
Creato: 2026-02-05 14:30:15
Scade: 2026-02-06 14:30:15

✓ Token valido!

Link di attivazione:
http://localhost:8000/activate.php?token=a3f7e9c1b2d4e5f6a7b8c9d0e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6u7v8w9x0

Codice da inserire nella pagina: K4X9M2
```

---

## 📋 Passo 3: Annota i Dati

Da quel output, copia:

1. **Link completo**:
   ```
   http://localhost:8000/activate.php?token=a3f7e9c1b2d4e5f6a7b8c9d0e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6u7v8w9x0
   ```

2. **Codice 6 cifre**:
   ```
   K4X9M2
   ```

---

## 🌐 Passo 4: Attiva Account nel Browser

### Metodo 1: Copia/Incolla Link
1. Copia tutto il link dal terminale
2. Incollalo nella barra indirizzi del browser
3. Premi INVIO

### Metodo 2: Manuale
1. Vai su: `http://localhost:8000/activate.php`
2. Verrà chiesto il link... ma è più facile il Metodo 1 😊

---

## ✍️ Passo 5: Inserisci Codice

La pagina mostra:

```
┌─────────────────────────────────────┐
│  🎵 Attivazione Account             │
├─────────────────────────────────────┤
│                                     │
│  ℹ️ Attivazione per: mario.rossi    │
│     mario.rossi@test.it             │
│                                     │
│  Codice Attivazione                 │
│  ┌─────────────────────────────┐   │
│  │   K 4 X 9 M 2              │   │
│  └─────────────────────────────┘   │
│                                     │
│  ℹ️ Inserisci il codice a 6 cifre  │
│     ricevuto via email              │
│                                     │
│  ┌─────────────────────────────┐   │
│  │  ✅ Attiva Account          │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

1. Inserisci: `K4X9M2` (il codice che hai copiato dal terminale)
2. Click **"Attiva Account"**

---

## ✅ Passo 6: Verifica Attivazione

### Successo! 🎉

```
┌─────────────────────────────────────┐
│  ✅ Account attivato con successo!  │
│     Ora puoi effettuare il login.   │
│                                     │
│  Account: mario.rossi               │
│                                     │
│  ┌─────────────────────────────┐   │
│  │  🔐 Vai al Login            │   │
│  └─────────────────────────────┘   │
└─────────────────────────────────────┘
```

### Verifica in Gestione Utenti

Torna a **Gestione Utenti** e vedrai:

| Username | Email | Ruolo | Stato |
|----------|-------|-------|-------|
| mario.rossi | mario.rossi@test.it | 📋 Segreteria | **✅ Attivo** |

---

## 🔄 Altri Esempi di Utilizzo

### Esempio con Email invece di Username:

```bash
php tests/get_activation_token.php mario.rossi@test.it
```

Stesso output!

---

### Esempio Utente Non Trovato:

```bash
php tests/get_activation_token.php utente.inesistente
```

**Output**:
```
❌ Utente non trovato: utente.inesistente
```

---

### Esempio Utente Già Attivo:

Se provi a recuperare il token di un utente già attivo:

```bash
php tests/get_activation_token.php admin
```

**Output**:
```
=== INFORMAZIONI UTENTE ===

Username: admin
Email: admin@musicall.it
Account Attivo: ✓ Sì

⚠️ Nessun token trovato per questo utente
Account già attivo - non serve token
```

---

## 🚨 Troubleshooting

### Token Scaduto

Se sono passate più di 24 ore:

```
=== TOKEN ATTIVAZIONE ===

Codice Attivazione: K4X9M2
Stato: SCADUTO
Creato: 2026-02-03 14:30:15
Scade: 2026-02-04 14:30:15

⚠️ Token scaduto - contatta l'amministratore per un nuovo token
```

**Soluzione**: L'admin deve creare un nuovo utente o implementare funzione "Reinvia email".

---

### Token Già Usato

Se hai già attivato l'account:

```
Codice Attivazione: K4X9M2
Stato: USATO

⚠️ Token già utilizzato
```

**Soluzione**: Account già attivo, puoi fare login!

---

## 📌 Riepilogo Veloce

```bash
# 1. Crea utente nella UI
# 2. Recupera token
php tests/get_activation_token.php mario.rossi

# 3. Copia link e codice dall'output
# 4. Apri link nel browser
# 5. Inserisci codice a 6 cifre
# 6. Click "Attiva Account"
# 7. ✅ Done!
```

---

## 🎓 Pro Tips

1. **Bookmark dello script**: Tieni il comando a portata di mano
2. **Copia il link intero**: Non copiare solo il token, ma tutto l'URL
3. **Maiuscole/Minuscole**: Il codice è case-insensitive (K4X9M2 = k4x9m2)
4. **24 ore**: Hai tempo fino al giorno dopo per attivare
5. **Auto-uppercase**: La pagina converte automaticamente in maiuscolo mentre digiti

---

**Fine esempio! Ora sai come attivare gli utenti in localhost!** 🎵