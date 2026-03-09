# MusicAll v2.2.1 - Release Notes

**Data Release:** 6 Marzo 2026

## Novità

### Gestione Iscrizioni - Filtro per Mese Accademico
- ✅ Implementato filtro per mese nella pagina **Gestione Iscrizioni**
- ✅ Visualizzazione iscrizioni valide nel mese selezionato (anno accademico Settembre-Luglio)
- ✅ Supporto per 12 mesi accademici con calcolo automatico dell'anno
- ✅ Sistema di log delle query per debugging

### Correzioni e Miglioramenti

#### Database
- **Data Fine Automatica**: Quando si crea un'iscrizione senza specificare `data_fine`, viene automaticamente impostata al 31 luglio dell'anno della `data_inizio`
- **Validazione Data Inizio**: Garantisce che `data_inizio` sia sempre presente (default: data corrente)

#### Controller
- **IscrizioniController**: Nuovo metodo `getIscrizioniPerMese()` per filtrare iscrizioni per mese accademico
  - Mostra iscrizioni attive durante il mese selezionato
  - Logica: `data_inizio <= ultimo_giorno_mese AND data_fine >= primo_giorno_mese AND stato = 'attiva'`
- **Logging**: Sistema di log in `logs/query_iscrizioni.log` con:
  - Timestamp
  - Mese selezionato
  - Parametri della query
  - Numero di risultati

#### Frontend (gestione_iscrizioni.php)
- **Rimozione Query Diretta**: Spostata tutta la logica dal file PHP al Controller (seguendo le linee guida di codifica)
- **Filtro Mese Accademico**: Dropdown con 12 mesi (Settembre-Luglio) con anno accademico
- **Ripristino Filtro**: Pulsante per tornare al mese corrente

#### Configurazione
- **LOG_PATH**: Nuova costante in `config/config.php` per il percorso dei log
- **Cartella Logs**: Creata directory `/logs` per archiviare i log dell'applicazione

## Specifiche Tecniche

### Query Filtro Iscrizioni
```sql
SELECT i.id, CONCAT(a.cognome, ' ', a.nome) as socio, ...
FROM iscrizioni i
INNER JOIN soci a ON i.socio_id = a.id
LEFT JOIN tipi_corso_config tcc ON i.tipo_corso_config_id = tcc.id
LEFT JOIN materie m ON i.materia_id = m.id
LEFT JOIN docenti d ON i.docente_id = d.id
WHERE i.stato = 'attiva'
AND i.data_inizio <= :ultimo_giorno_mese
AND i.data_fine >= :primo_giorno_mese
ORDER BY a.cognome, a.nome ASC
```

### Anno Accademico
L'anno accademico inizia a **Settembre** e termina a **Luglio**:
- Settembre 2025 - Luglio 2026 = Anno accademico 2025-2026
- Settembre 2026 - Luglio 2027 = Anno accademico 2026-2027

Calcolo automatico nella pagina:
```php
$anno_accademico_inizio = ($mese_corrente >= 9) ? $anno_corrente : $anno_corrente - 1;
```

## Testing

- ✅ Filtro per mese funzionante
- ✅ Iscrizioni create con data_fine automatica
- ✅ Log query salvato correttamente
- ✅ Visualizzazione corretta iscrizioni per mese selezionato
- ✅ Persistenza iscrizioni nei mesi successivi (es: iscrizione aperta a marzo visibile anche ad aprile)

## File Modificati

1. `config/config.php` - Versione 2.2.1 + LOG_PATH
2. `includes/Controllers/IscrizioniController.php` - Nuovo metodo getIscrizioniPerMese()
3. `gestione_iscrizioni.php` - Rimosso query diretto, integrato controller
4. `logs/` - Cartella nuova per archiviare i log

## Note per lo Sviluppatore

- ✅ Seguire le linee guida: NO query nei file PHP frontend
- ✅ Usare sempre controller/helper per la logica
- ✅ Implementare logging per debugging
- ✅ Testare filtri per date in SQLite

## Versioni Precedenti

- v2.2.0 - Sistema eventi calendario completato
- v2.1.0 - Sistema iscrizioni e pagamenti
- v2.0.0 - Refactoring architettura
- v1.0.0 - Release iniziale
