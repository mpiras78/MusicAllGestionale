# Integrazione eventi_calendario in calendario.php

## Problema
Il calendario mostra solo le lezioni dalla tabella `lezioni`, ma NON mostra:
- ❌ Recuperi (salvati in `eventi_calendario` con tipologia LEZ_RECUPERO)
- ❌ Prenotazioni rapide (salvate in `eventi_calendario` con tipologie PREN_*)

## Soluzione
Modificare `calendario.php` per caricare ENTRAMBE le fonti:
1. Lezioni ricorrenti dalla tabella `lezioni`
2. Eventi specifici dalla tabella `eventi_calendario`

## Implementazione

### 1. Modificare la query di caricamento dati

**PRIMA** (solo lezioni):
```php
$lezioni = $lezioniCtrl->getLezioniPerGiorno($giorno_selezionato, true, $data_selezionata);
```

**DOPO** (lezioni + eventi):
```php
// Carica lezioni ricorrenti
$lezioni = $lezioniCtrl->getLezioniPerGiorno($giorno_selezionato, true, $data_selezionata);

// Carica eventi specifici per questa data
$db = Database::getInstance()->getConnection();
$stmt = $db->prepare("
    SELECT 
        e.id,
        e.ora_inizio,
        e.ora_fine,
        e.aula_id,
        t.codice as tipo,
        t.nome as tipologia_nome,
        t.colore_bg,
        t.colore_border,
        COALESCE(a.cognome || ' ' || a.nome, '') as socio,
        COALESCE(a.id, 0) as socio_id,
        COALESCE(d.cognome || ' ' || d.nome, '') as docente,
        COALESCE(m.nome, e.titolo, 'Prenotazione') as materia,
        au.nome as aula,
        e.note,
        e.attivo as attiva,
        e.confermato
    FROM eventi_calendario e
    INNER JOIN tipologie_evento t ON e.tipologia_id = t.id
    LEFT JOIN soci a ON e.socio_id = a.id
    LEFT JOIN docenti d ON e.docente_id = d.id
    LEFT JOIN materie m ON e.materia_id = m.id
    LEFT JOIN aule au ON e.aula_id = au.id
    WHERE e.data_evento = ?
    AND e.attivo = 1
    AND t.attiva = 1
");
$stmt->execute([$data_selezionata]);
$eventi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Unisci lezioni + eventi
$tutte_lezioni = array_merge($lezioni, $eventi);
```

### 2. Modificare rendering per supportare diversi tipi

Aggiungere supporto per i codici tipologia:
- LEZ_REGOLARE → tipo 'regolare'
- LEZ_RECUPERO → tipo 'recupero'
- PREN_SALA_SOCI → tipo 'prenotazione-soci'
- PREN_DOCENTE → tipo 'prenotazione-docente'
- PREN_ESTERNO → tipo 'prenotazione-esterno'

### 3. Aggiungere stili CSS per prenotazioni

```css
/* Prenotazioni */
.lezione-slot.tipo-prenotazione-soci {
    background-color: #e8f5e9;
    border-left: 3px solid #4caf50;
}

.lezione-slot.tipo-prenotazione-docente {
    background-color: #fff9c4;
    border-left: 3px solid #fdd835;
}

.lezione-slot.tipo-prenotazione-esterno {
    background-color: #ffebee;
    border-left: 3px solid #ef5350;
}
```

### 4. Aggiornare legenda

Aggiungere nella legenda le nuove tipologie di eventi visualizzabili.

## File da Modificare

1. `calendario.php` - logica caricamento e rendering
2. `assets/css/style.css` - stili per nuovi tipi
3. `includes/controllers/LezioniController.php` - eventualmente creare metodo unificato

## Benefici

✅ Visualizzazione completa di tutti gli eventi
✅ Recuperi visibili nel calendario
✅ Prenotazioni visibili nel calendario
✅ Colori diversi per distinguere le tipologie
✅ Sistema coerente e completo

## Priorità

🔴 **ALTA** - Funzionalità core, gli utenti devono vedere recuperi e prenotazioni