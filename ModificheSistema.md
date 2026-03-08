## 📖 Modifiche al sistema

# Dati Associazione
Creare una nuova tabella nel database in cui creare i campi:
- ragione_sociale
- indirizzo
- cap
- citta
- codice_fiscale
- telefono
- email
- pec
# Pagina dati Associazione
Creare una nuova pagina accessibile solo da amministratore che permetta di inserire e/o modificare i dati dell'associazione:
- Ragione Sociale
- Indirizzo
- Cap
- Città
- Codice fiscale
- Telefono
- Email
- PEC

## 🎯 Gestione iscrizioni e corsi lato segreteria
Cambiare tutte le lable Allievo con Socio
Queste funzionalità sono visibili solamente per ruoli segreteria e amministratore
# Iscrizione Socio annuale
Aggiungere al menù gestioni una nuova voce Gestione Costi iscrizione
# Pagina configurazione costi iscrizione
 Creare una nuova pagina che si apra sul click del menù "Gestione Costi iscrizione" per la configurazione dei costi di iscrizione, con 2 tipi di costi : uno valido per iscrizioni da Agosto a Febbraio, l'altro costo da Marzo al 31 Luglio
# dettaglio Anagrafica 
Nella pagina di nuovo socio (quella che attualmente è la nuova iscrizione) aggiungere un flag "sconto familiare" nel caso in cui un familiare sia già iscritto nello stesso anno. In caso di selezione flag l'iscrizione diventa gratuita, quindi a costo 0
# Sconto familiare - processo
Nel caso in cui venga selezionato il flag sconto familiare dalla pagina di una nuova iscrizione, far comparire un box di ricerca soci in cui cercare il familiare già iscritto e collegarlo ad una o più anagrafiche dei familiari. Lo scopo di avere un collegamento è che se si applica uno sconto familiare ad un nuovo socio e nei mesi successivi l'altro familiare interrompe i corsi, lo sconto non deve più essere applicato
Lo sconto si applica solamente a partire dal pagamento mensile in cui non c'è più nessun familiare a frequentare corsi. Se il pagamento del corso mensile è già avvenuto non succede nulla.
# Campi della pagina nuova iscrizione
 - Nella pagina nuova iscrizione prevedere la possibilità di inserire 2 numeri di telefono cellulare e aggiungere  cap e città. Non serve mantenere lo storico delle modifiche , traccia solo l'attività di modifica informazioni utente sulla tabella di audit con utente che ha effettuato la modifica, orario della modifica, dato originale e dato modificato. Il dato originale e modificato può essere gestito tramite campo testuale. Aggiungere comunque la data inserimento e data modifica a tutte le tabelle
# dettaglio Anagrafica 2
L'iscrizione ha durata annuale, quindi una volta effettuata una iscrizione ha durata per tutto l'anno accademico, a partire dal mese di iscrizione fino al 31 luglio dell'anno accademico. Solitamente in concomitanza con l'iscrizione annuale ( si diventa soci per l'anno accademico e si ha possibilità di iscriversi anche ai  corsi. L'iscrizione è sempre attiva, si può invece sospendere temporaneamente o definitivamente un corso. 
Al momento dell'iscrizione generare un numero tessera composto da anno e progressivo che parte da 1 e si incrementa via via:  es 20261 [Creare tabella di relazione numero tessera-anagrafica]. Per progressivo si intende un numero progressivo numerico che parte da 1 per il primo iscritto dell'anno accademico che parte a Settembre. Le tessere sono annuali, per l'anno successivo sarà necessaria una nuova iscrizione.Il progressivo è comune per tutti i soci, è come se fosse un contatore della tabella iscrizioni
# Stampa iscrizione
Al termine del wizard di nuova iscrizione va inserito un pulsante di stampa per stampare l'iscrizione da far poi firmare al nuovo socio. I dati da inserire nella pagina di stampa sono:Intestazione: Domanda di iscrizione anno (anno accademico),  Dati associazione , dati socio comprensivo di numero tessera, data e firma per presa visione del regolamento.
Per l'invio della mail ti fornirò io il codice sorgente


# Gestione Corsi: dettagli
# Azioni elenco soci 1
Nella pagina di elenco soci ( attualmente gestione_allievi ) inserire una icona nelle azioni per modificare i corsi a cui è iscritto il socio. Il click su questa nuova icona entra in una nuova pagina con le informazioni anagrafiche del socio in alto come riepilogo, l'elenco dei corsi associati con tasto di modifica e cancellazione e un pulsante per l'aggiunta di un nuovo corso
# Modifica iscrizione
La pagina di modifica iscrizione mostra già i dati anagrafici completi del socio, permette la modifica della tipologia di corso , data, ora, sala , materia e insegnante. Il pulsante modifica applica una data fine al corso precedente e ne crea uno nuovo, in modo da mantenere lo storico e calcolare in modo corretto il prezzo nella pagina di pagamento. Quando mi troverò nella pagina di caricamento dovrai effettuare il calcolo preciso, esempio:
- Corso X di lunedì , disattivato in data dd/mm/yyyy , numero lunedì del mese=1 , costo 200€ al mese ( intesto per 4 settimane), calcolo (200:4) * 1
- Corso Y di lunedì , attiva da data dd/mm/yyyy , numero lunedì del mese = 3, costo del corso 250€ al mese ( intesto per 4 settimane), calcolo: (250:4)*3
Il pagamento dell'iscrizione avviene mensilmente, quindi ogni mese si ricalcola in base alle giornate di corso presenti nel mese al netto di festività o chiusure attività
## Azioni elenco soci 2
 Nella pagina di elenco soci aggiungere un tasto per i pagamenti
 Aggiungere una colonna "pagato" con cerchio rosso o cerchio verde nel caso in cui non risulti il pagamento del mese corrente 
 Il pulsante elimina elimina veramente se il socio non ha corsi o pagamenti associati
 # Pagina di dettaglio socio
 Il pulsante dettaglio Socio ( deve diventare modifica socio ) deve visualizzare anche il calcolo del pagamento del mese corrente ( ricordati che il costo di ogni corso è basato su 4 settimane, se il giorno in cui fa lezione il socio nel mese corrente ha più o meno ricorrenze, il costo va ricalcolato ), quindi il costo può variare di mese in mese e da socio a socio a seconda del giorno in cui fanno lezione anche se sono iscritti allo stesso corso. I giorni di festività escludono la presenza di lezioni.
 Le festività sono quelle presenti dal calendario italiano standard, in più va considerata una tabella in cui inserire i periodi di chiusura attività.
 
## Gestione corsi : dettaglio
 - Cambiare voce "Allievi" del menu Gestione con Elenco Soci

## ASSENZE E RECUPERI
- Nell'elenco dei recuperi aggiungere tasto "modifica" per la modifica delle informazioni di recupero
- Nell'elenco delle assenze, se il periodo di assenza è stato già completamente recuperato nascondere l'icona di "Programma recupero" nelle azioni
- Se l'orario del recupero non copre completamente l'orario di assenza, permettere di inserire un nuovo recupero solamente per i rimanente tempo da recuperare.
- Nella pagina di inserimento nuovo recupero mostrare il tempo da recuperare , totale o parziale se esiste già un recupero che copre parzialmente il tempo totale dell'assenza
- Creare una pagina di configurazione accessibile solo da utenza con ruolo admin per la definizione del numero di recuperi garantiti. SI definisce un numero X, dopo di cui se l'allievo ha già recuperato quel numero X di volte, il recupero diventa opzionale
- Al superamente delle assenze causate da socio predisporre invio email da inviare al socio allievo con riepilogo recuperi effettutati. Nella email inserire l'elenco completo delle assenze e recuperi già effettuati

## PAGAMENTI
Creare nuova tabella pagamenti
Creare tipologia pagamento con voci 
- iscrizione
- corso
L'iscrizione fa riferimento all'iscrizione annuale,mentre il corso, alle lezioni (corsi) seguiti da un socio
Il pagamento è manuale quando si clicca sul pulsante paga ( accessibile dall'elenco dei soci se manca il pagamento del mese o dal dettaglio del socio nelle stesse condizioni)
Il pagamento per bonifico non viene riconciliato 
# Pagina di pagamento
Nella pagina di pagamento controllare se ci si trova in uno dei seguenti casi:
 - altro familiare già iscritto nello stesso anno accademico ( controllare le associazioni con i familiari )
 - stesso socio già iscritto ad altro corso nello stesso anno accademico
 - sconto compleanno ( se nel mese corrente il socio compie gli anni )
 - sconto sul prezzo di iscrizione annuali per soci mattutini se si iscrivono a corsi dalle 9:00 alle 13:00: iscrizione la metà e sconto su tutti i mensili del 10%
 Se si rientra in una di questi scenari, mostrare con un Alert dedicato e suggerire l'applicazione di uno sconto. Gli sconti non sono cumulabili.
 Lo sconto applicabile si visualizza nella pagina di pagamento con un flag, l'attivazione o meno dello sconto ( e successivo ricalcolo del prezzo ) è a discrezione dell'utente di tipo "segreteria" o "admin"
 Nella pagina di pagamento evidenziare il conteggio delle giornate di lezione per ogni corso ed eventuali festività che ricadono su un corso o sull'altro 
# Tabella famiglia
Creare una tabella famiglia per il collegamento familiare, in cui inserire i campi: id socio che si sta iscrivendo, id del socio familiare iscritto
Nella pagina di pagamento va controllato se il socio familiare ha ancora almeno un corso attivo
# Modalità di pagamento
Creare nuova tabella modalità di pagamento con voci
- Contanti
- Bonifico
# stampa
 - La pagina di pagamento deve mostrare i dati del socio comprensivi di numero tessera, l'iscrizione annuale se ancora non risulta pagata e i corsi associati con calcolo del costo per ogni voce di pagamento, per poi fare il calcolo totale.
 - La pagina di pagamento è accessibile dall'icona pagamento presente nella pagina di elenco soci
 - Nella pagina di pagamento deve essere presente il tasto di stampa per la stampa dei dati di pagamento: Intestazione dati associazione, dati anagrafici socio, riepilogo corsi ( ed eventualmente iscrizione annuale) e totale con firma.
 Una copia deve essere inviata alla mail del socio.
# Batch 
creare una procedura automatica che ogni 26 del mese invia promemoria via email a tutti gli iscritti con il promemoria per il pagamento che contenga corsi associati, numero di lezioni nel mese e calcolo finale. Dovendo deployare il gestionale in uno spazio aruba su cui non ho controllo macchina per la definizione di un cron job, aggiungi qualche liberia per la gestione via api dello scheduling, crea una tabella batch ad hoc e crea una pagina di gestione. 
# Libreria cron
https://github.com/pmill/php-scheduler

# Grafica
Sul calendario nel box delle lezioni, il nome e cognome del socio devono avere lo stesso colore della sala in cui fanno lezione
Nella pagina Gestione_Allievi nella colonna Lezioni devono essere visualizzati i corsi effettuati dal socio: Batteria  con Insegnante x, Laboratorio y
# Modifiche al menù
Rimuovi la voce di menù Gestione -> Lezioni
Aggiungi al menù principale la voce Nuova iscrizione
Modifica la voce di menù Gestione -> Iscrizioni con Gestione->Corsi. La pagina deve contenere le stesse statistiche della pagina gestione_lezioni con gli stessi filtri, in più contenere anche il filtro per mese come la pagina gestione_iscrizioni
Le informazioni da visualizzare devono essere: 
- Cognome e nome Socio
- Tipologia corso
- Materia
- Insegnante
- Aula
- Giorno della settimana
- Orario
- Mensilità pagata [SI/NO]
Le azioni possibili devono essere Dettaglio e Sospensione
- La pagina di dettaglio aperta in modale mostra il riepilogo di tutte le informazioni, compresi dati anagrafici del socio, più l'elenco di tutti i pagamenti alla fine
- Il pulsante di sospensione mostra una modale per la selezione della data da cui ha effetto la sospensione di quel corso . La sospensione del corso può essere momentanea ( per uno o più mesi ) o definitiva. Per sospensione temporanea inserire nel calendario una icona simile all'assenza che ricordi lo slot del corso sospeso, con possibilità di essere occupato da un altro corso, recupero, prenotazione sala o altro. Se viene inserito un altro corso nello stesso slot, inserire un alert in fase di registrazione nuovo corso ( o modifica corso esistente ), per evidenziare la sovrapposizione.
Per sospensione definitiva, eliminare tutte eventuali ricorrenze future di quel corso già inserite.

# Tabelle
-- Già suggerito ma confermare:
- dati_associazione (✓ ben definito)
- soci (attualmente allievi - rinominare)
- iscrizioni_annuali (per tracciare iscrizioni)
- corsi_soci (il mapping socio-corso attuale)
- pagamenti_storico (per tracciare ogni transazione)
- numero_tessera (per collegarsi a socio, non creerei una tabella, ma numero tessera può essere un campo di iscrizioni annuali)
- famiglia (per il collegamento familiare)
- batch_runs (per log dei batch)

# Audit
Deve esistere una tabella di audit che contiene tutte le attività e le modifiche fatte a tutte le tabelle, che contenga le seguenti informazioni:
- utente che effettua ila modifica
- data modifica
- tipo modifica : modifica corso, rimozione corso, inserimento assenza, inserimento recupero , etc etc, 
- vecchia informazione
- nuova informazione
Nel caso in cui si aggiunge semplicemente un nuovo allievo le informazioni saranno:
- utente che effettua la modifica: Mario Rossi
- data modifica: 26/09/2025 10:00
- tipo modifica: inserimento nuovo socio
- vecchia informazione: nessuna
- nuova informazione: Mario Rossi, Via Roma 1, 00100 Roma, 1234567890

# Ruoli
- Amministratore: accesso completo a tutte le funzionalità e dati
- Segreteria: accesso completo al calendario, assenze, recuperi, nuove iscrizioni, corsi, pagamenti, modifiche. NOn ha accesso alla configurazione dei costi, alla visualizzazione delle statistiche di contabilità
- Docente: accesso al calendario alle proprie lezioni, da cui può segnare assenze o organizzare recuperi

# Risposte in breve
Sconto familiare retroattivo o prospettico? Prospettico
Socio può avere più corsi simultaneamente? sì
Iscrizione annuale è OBBLIGATORIA o FACOLTATIVA? Obbligatoria per frequentare corsi
Chi approva i pagamenti? (auto-fattura? rimessa a banca?) Skip per ora
Gestite bonifici o solo contanti? Sia bonifici che contanti
Backup annuale dei dati? (es. archivio 2025 separato) skip per il momento

## Problei critici
# Gestione sconto familiare
se un socio con sconto familiare interrompe i corsi, come gestire la revoca dello sconto al familiare? Al pagamento successivo con i controlli già descritti tramite tabella familiare e check del corso familiare ancora attivo
# Esempio
- Mario Rossi si iscrive a Settembre 2025 con sconto familiare perchè la sorella è già iscritta, quindi il costo di iscrizione è 0 e il costo del corso è scontato del 10% ( vedere tabella sconti )
- Il mese successivo la sorella di Mario Rossi interrompe i corsi. QUando andrà a pagare Mario Rossi perderà lo sconto perchè il suo familiare registrato non avrà un corso attivo
- La sorella di Mario ROssi vuole iniziare nuovamente a frequentare un corso e avrà diritto allo sconto familiare 
# Conflitto corsi
Corso momentaneamente sospeso con slot occupato da altro corso, come lo risolvo? Non si può risolvere, va evitato inserendo un alert nella pagina di inserimento/modifica nuovo corso/iscrizione e in caso di sovrapposizione non gestita nell'immediato ( salvataggio confermato di corsi sovrapposti ) va gestito come reminder costante da visualizzare nella home page.
# Nuova tabella di alert
Creare nuova tabella Alert con messaggio specifico, riferimento ad anagrafica socio / Numero tessera socio con sospensione, nuovo socio in sovrapposizione, giorno, orario e sala in cui c'è la sovrapposizione. 
Possibilità di eliminare il messaggio solo da Segreteria o Admin. ( In ogni caso va salvata la cancellazione nella tabella di audit)
 # Iscrizione
 L'iscrizione ha durata annuale, quindi il pagamento deve essere effettuato solamente una volta nel corso dell'anno accademico indipendentemente da sospensioni corsi, nuovi corsi, riattivazione corsi da parte dello stesso socio. L'iscrizione si paga dalla pagina di pagamento. Se il socio ha un corso che inizierà ad esempio a Settembre ma vuole pagare l'iscrizione ad agosto, da pagina di pagamento ad agosto risulterà solo il costo di iscrizione perchè il corso non inizierà prima di Settembre

# Scenario confuso
Socio ha 2 corsi: Chitarra (lunedì) + Batteria (mercoledì)
Marzo: Chitarra ha 4 lunedì (€50), Batteria ha 5 mercoledì (€75) = €125 totale
Paga con bonifico: Nessuna riconciliazione
Aprile: Chitarra ha 5 lunedì (€62,50), Batteria ha 4 mercoledì (€60) = €122,50 totale
Come traccio quale mese ha pagato? Al ricevimento del bonifico viene marcato come pagata la quota di QUEL MESE. Mettere a disposizione un tasto stampa sganciato dal pagamento in modo da poter stampare la ricevuta di pagamento in qualsiasi momento.
Tutti i pagamenti dei corsi sono mensili

# AMBIGUITÀ: Sconti Mutuamente Esclusivi
L'elenco di eventuali sconti applicabili va mostrata nella pagina di pagamento e sono selezionabili dalla segreteria o dall'admin.
Gli sconti sono esclusivi, quindi non comulabili

# SCENARIO:
Socio si iscrive a febbraio con costo "Agosto-Febbraio"
Il mese successivo (marzo), cambia a costo "Marzo-Luglio"
Chiedo il pagamento della differenza? O non cambia niente? Il costo iscrizione annuale non cambia mai, è annuale e dipende dal mese di iscrizione

# Scenario ferie possibile:
Lunedì 25 Aprile (festa nazionale) = nessuna lezione
Associazione chiusa dal 20 al 22 Agosto = nessuna lezione
Socio ha corso il lunedì
Marzo: 4 lunedì (normali)
Aprile: 4 lunedì ma 25 aprile è festivo = 3 lezioni effettive
Come informo il socio? "Il mese scorso hai avuto 3 lezioni al posto di 4"? La segreteria al momento del pagamento. Tutti i soci ricevono comunque un regolamento in cui è ben spiegato
Chi definisce le chiusure? Solo admin? SI

# DOMANDE assenze e recuperi:
Es: Recuperi garantiti = 3
Allievo ha 5 assenze
Ha già i primi 3 recuperi, gli altri sono "opzionali"? SI
Chi decide se farli? (Docente? Segreteria?) Insieme Docente e Segreteria in base alla disponibilità del docente e alla disponibilità delle sale
Non si può programmare il recupero 4 e 5?Come sopra, si programma a discrezione della segreteria e della disponibilità dell'insegnante

#  INCOERENTE: Email di Recuperi
La mail di riepilogo assenze va inviata quando si registra la seconda assenza per il socio

#  MANCA COMPLETAMENTE: Modifica Pagamenti
Nella pagina di dettaglio socio, se il mese corrente non risulta pagato, deve essere presente un tasto "Paga" che rimanda alla pagina di pagamento. Se il mese è già stato pagato, invece, deve essere presente un tasto "Stampa ricevuta" per stampare la ricevuta di pagamento. La ricevuta di pagamento è una pagina con i dati del socio, i corsi associati e il totale pagato, con intestazione dei dati dell'associazione e spazio per la firma. Una copia della ricevuta deve essere inviata alla mail del socio.
Un pagamento può essere modificato solo da ruolo admin 
Un pagamento può essere cancellato solo da ruolo admin e solo se ha come forma di pagamento "contanti"
Aggiungere una nota alla pagina di pagamento ( e quindi alla tabella pagamenti )
Tracciare tutte le modifiche su tabella di audit ( tranne le cancellazioni dei pagamenti )

# Nuova iscrizione
La vece Nuova Iscrizione nel menù principale crea una nuova iscrizione socio
La voce Gestione -> Iscrizioni gestisce le iscrizioni esistenti

# Modifica Anagrafica Socio
Socio cambia indirizzo a febbraio
Stampa ricevuta pagamento di marzo - quale indirizzo metto? (febbraio o vecchio?) Ovviamente quello di febbraio che è l'unico presente. Il vecchio è presente solo nella tabella di 

 # Corso "Sospeso Temporaneamente"
 Nella pagina di sospensione abbiamo già detto che c'è la sospensione temporanea e quella definitiva. QUella temporanea obbliga ad inserire i mesi di sospensione. 
 Il caso di sospensione momentanea lo slot nei mesi di sospensione sono liberi ( ma ricordati la gestione degli alert se creo un altro corso nello stesso slot ) ma resta un "segnalibro con nota" simile a quello delle lezioni annullate

# Sconto mattutino
Socio iscrive corso alle 14:00 (pomeridiano) a settembre = paga prezzo pieno
Novembre cambia corso alle 10:00 (mattutino) - L'iscrizione è già pagata con valenza annuale, quindi non cambia nulla. Nessuno sconto retroattivo
Si applicherà solo lo sconto al corso a partire dal mese in cui inizierà il corso mattutino?

# Dubbi alert su conflitto corsi
Se creo sospensione da marzo a maggio, creo 3 alert (uno per mese) o 1 alert "generale"? 1 Alert unico
Chi visualizza gli alert? segreteria e admin 
Alert è ancora visibile se ho risolto il conflitto? L'alert scompare quando admin elimina l'alert ( Non è una cancellazione ma una disabilitazione, prevedere un flag visibile che può assumere valori 1 = visibile oppure 0=non visibile).
Creare pagina per visualizzare alert con accesso solo da utente admin
Timeout dell'alert - dopo quanto tempo scompare automaticamente? Mai

# Pagamento iscrizione
Se socio iscrive a settembre e paga iscrizione
A novembre aggiunge un nuovo corso
L'iscrizione rimane PAGATA per tutto l'anno? La quota di iscrizione si, il corso poi ha il suo costo specifico mensile

# Assenze
Esempio : la configurazione del numero massimo di assenze è impostato a 2
SOcio 1 fa la prima assenza -> da recuperare
Socio 1 fa la seconda assenza -> da recuperare -> invio email reminder con elenco assenze
Socio 1 fa la terza assenza -> recupero facoltativo  

# tasto stampa ricevuta
Va inserita una icona nell'elenco soci e un tasto nella pagina di pagamento

# Tracciamento cancellazione pagamenti
Non tracciare i pagamenti eliminati da utenza admin

# Sconto mattutino
Va applicato dalla pagina di pagamento, si applica se nel mese in corso ho un corso mattutino e si applica solo a quel corso. 
Tutti gli sconti sono suggeriti e da confermare in pagina di pagamento tramite flag da admin o segreteria

# Mail pagamento
Va inviata automaticamente al salvataggio del pagamento

# Festività
Se creo ALERT ogni volta che creo un corso sovrapposto, l'utente non vede mille alert? SI
Limite: Massimo X alert per socio? NO 
Come cancello l'alert dopo aver risolto? Può eliminare solo admin

# Tabella festività
Calendario italiano standard da calcolare tramite api


# Checklist chiarimenti
[ ] Tabella Alert - Tracciamento e visualizzazione 
- Gli alert finora individuati sono le sovrapposizioni tra corsi sospesi e nuovi corsi nello stesso slot orario e sala o che in quella sala occupa in una fascia oraria che vada in sovrapposizione parziale. L'alert resta visibile finchè l'utente con ruolo admin non lo marca come "letto" e lo nasconde "stato visibile=0".
[ ] Iscrizione + Corsi - Quando si paga il secondo corso? Ci sono due entità: iscrizione e corso. L'iscrizione si paga una sola volta, i corsi si pagano mensilmente. Il secondo corso si paga solitamente in uno spazio temporale che va da una settimana prima l'inizio del corso ad una settimana dopo l'inizio. Legare il pagamento ad una mensilità in modo di non confondere il mese in cui avviene il pagamento con la mensilità di riferimento
[ ] Recuperi Garantiti - Limite per anno o per assenza? Il limite di recupero è annuale. Per recuperare interamente l'orario di una assenza, possono essere creati più recuperi.
esempio: il socio fa una assenza di 1 ora il lunedì. Non ci sono slot da un ora liberi a disposizione ma c'è la possibilità di fare 15 minuti in più di lezione ogni volta. Per 4 lunedì verrà creato un recupero di 15 minuti a ridosso della lezione
[ ] Stampa Ricevuta - Dove si trova (dettaglio socio o pagamento)? In entrambe i posti
[ ] Audit - Tracciare anche le cancellazioni di pagamenti? NO
[ ] Sconto Mattutino - Chi paga la differenza a novembre? Nessuno, non esiste la differenza o lo sconto retroattivo. 
[ ] Email Ricevuta - Automatica o manuale? Automatica, va sempre inviata una copia alla mail dell'associazione ( campo email tra i dati dell'associazione )
[ ] Alert Cumulativi - Limite massimo? NO
[ ] Tabella Festività - Hardcoded o customizzabile? Le ricorrenze dei giorni cambiano di anno in anno, quindi vanno calcolati a runtime. Nella tabella di chiusura verranno inseriti giorni singoli di chiusura in caso di festività non riconosciute dal sistema
[ ] Modifica Corso - Pro-rata su primo mese?
Scenario Preciso:
Cambio corso il 15 marzo
Corso vecchio (lunedì):
Dal 1-14 marzo = 2 lunedì
Costo: (prezzo/4) × 2
Corso nuovo (mercoledì):
Dal 15-31 marzo = 3 mercoledì (approssimativo)
Costo: (prezzo/4) × 3
Come avviene il pagamento di marzo?
Dipende: Quando viene fatta la modifica, bisogna controllare lo stato del pagamento per quel mese. Se il socio ha già effettuato il pagamento del primo corso bisogna fare il calcolo del pagamento che rispecchia la nuova configurazione e poi sottrarre il nuovo prezzo al pagamento effettuato. Se c'è un delta positivo va salvato il pagamento del delta con la stampa di una nuova ricevuta. In questo caso nella pagina di riepilogo pagamenti del mese, deve esserci traccia di entrambe le transazioni