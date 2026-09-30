# Test E2E Playwright

I test E2E verificano l'interfaccia con un browser reale e sono complementari ai test PHPUnit in `tests/Unit`, `tests/Integration` e `tests/Feature`.

## Esecuzione

Avvia l'applicazione su un database di test, quindi imposta credenziali amministrative dedicate:

```powershell
$env:E2E_BASE_URL = 'http://127.0.0.1/MusicAllGestionale'
$env:E2E_ADMIN_USERNAME = 'e2e_admin'
$env:E2E_ADMIN_PASSWORD = 'pe3upuroCe'
npm run test:e2e
```

Senza le credenziali, i test che richiedono autenticazione sono contrassegnati come skipped. I test E2E non devono essere eseguiti sul database operativo.

## Convenzioni

- Un file spec copre un requisito, con nome `fr-xx-descrizione.spec.js`.
- I flussi riutilizzabili risiedono in `pages`; autenticazione e fixture in `fixtures`; configurazione e helper in `support`.
- I test usano `data-testid`, non classi CSS, testo variabile o struttura Bootstrap.
- I selettori nuovi vanno aggiunti ai Page Object, non duplicati nelle spec.
