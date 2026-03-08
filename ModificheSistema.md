# 📖 Modifiche al sistema

## 🎯 Gestione iscrizioni e corsi lato segreteria
Dividere l'inserimento delle iscrizioni dall'inserimento di un nuovo corso per iscritto.
Es. Faccio l'iscrizione annuale a partire dal mese di Ottobre e mi iscrivo a un corso, a gennaio posso iscrivermi ad un altro corso e l'iscrizione è ancora valida perchè annuale.
Queste funzionalità sono visibili solamente per ruoli segreteria e amministratore.

## Gestione iscrizioni: dettagli
## dettaglio iscrizione 1 
Creare pagina di configurazione costi e sconti in cui inserire 2 costi iscrizione : uno valido per iscrizioni da Agosto a Febraio, l'altro costo da Marzo al 31 Luglio
## dettaglio iscrizione 2
L'iscrizione ha durata annuale, quindi una volta effettuata una iscrizione ha durata per tutto l'anno accademico, a partire dal mese di iscrizione fino al 31 luglio.
Al momento dell'iscrizione generare un numero tessera composto da anno e progressivo che parte da 1 e si incrementa via via:  es 20261
# dettaglio iscrizione 3
Nella pagina di nuova iscrizione aggiungere un flag "sconto familiare" nel caso in cui un familiare sia già iscritto nello stesso anno. In caso di selezione flag l'iscrizione diventa gratuita, quindi a costo 0
# dettaglio iscrizione 4
Nella pagina di dettaglio dell'iscrizione mostrare i corsi a cui è iscritto l'allievo, con la possibilità di aggiungere nuovi corsi 

---

## Gestione corsi : dettaglio
## DETTAGLIO corsi 1
 pagina di configurazione costi e sconti aggiungere lo sconto da applicare per i nuovi corsi nei seguenti casi:
 - altro familiare già iscritto nello stesso anno accademico 
 - stesso socio già iscritto ad altro corso nello stesso anno accademico
 - sconto compleanno
