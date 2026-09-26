# Release Notes v2.5.0

## Data: 2026-03-09

### Major Changes
- Refactor completo: sostituzione di 'allievo/allievi' con 'socio/soci' in tutta la codebase (UI, script, controllers, SQL, DB).
- Aggiornamento naming e documentazione.
- Migrazione DB: rinomina tabella 'allievi' in 'soci', colonne 'allievo_id' in 'socio_id'.
- Aggiornamento modelli: aggiunto Socio.php, rimosso Allievo.php.
- Fix bug SQL e JS, aggiornamento fetch path API.
- Tutti i test e lint passano senza errori.

### Minor Changes
- Cleanup codice legacy.
- Aggiornamento batch controller e query.

### Note
- Verificare deploy su ambiente di staging.
- Seguire le istruzioni di migrazione per aggiornamento DB.

---

Per dettagli consultare [`README.md`](../../README.md) e la
[guida alla test suite](../guides/DOCUMENTAZIONE_TEST_SUITE.md).