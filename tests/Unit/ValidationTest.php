<?php
/**
 * Test Validazione Dati
 * 
 * Test per validazione input e business rules:
 * - Validazione form allievi
 * - Validazione lezioni
 * - Regole business
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ValidationTest extends TestCase
{
    /**
     * Test: Validazione dati allievo completi
     */
    public function testValidazioneDatiAllievoCompleti()
    {
        $validaAllievo = function($dati) {
            $errori = [];
            
            if (empty($dati['nome'])) $errori[] = 'Nome obbligatorio';
            if (empty($dati['cognome'])) $errori[] = 'Cognome obbligatorio';
            if (!empty($dati['email']) && !filter_var($dati['email'], FILTER_VALIDATE_EMAIL)) {
                $errori[] = 'Email non valida';
            }
            
            return empty($errori) ? true : $errori;
        };
        
        $datiValidi = [
            'nome' => 'Mario',
            'cognome' => 'Rossi',
            'email' => 'mario@example.com'
        ];
        
        $this->assertTrue($validaAllievo($datiValidi));
    }
    
    /**
     * Test: Validazione dati allievo incompleti
     */
    public function testValidazioneDatiAllievoIncompleti()
    {
        $validaAllievo = function($dati) {
            $errori = [];
            
            if (empty($dati['nome'])) $errori[] = 'Nome obbligatorio';
            if (empty($dati['cognome'])) $errori[] = 'Cognome obbligatorio';
            
            return empty($errori) ? true : $errori;
        };
        
        $datiInvalidi = ['nome' => 'Mario'];
        $risultato = $validaAllievo($datiInvalidi);
        
        $this->assertIsArray($risultato);
        $this->assertContains('Cognome obbligatorio', $risultato);
    }
    
    /**
     * Test: Validazione orario lezione
     */
    public function testValidazioneOrarioLezione()
    {
        $validaOrario = function($ora_inizio, $ora_fine) {
            $start = strtotime($ora_inizio);
            $end = strtotime($ora_fine);
            
            if ($end <= $start) return 'Ora fine deve essere dopo ora inizio';
            if (($end - $start) < 900) return 'Durata minima 15 minuti';
            if (($end - $start) > 10800) return 'Durata massima 3 ore';
            
            return true;
        };
        
        $this->assertTrue($validaOrario('14:00', '14:45'));
        $this->assertIsString($validaOrario('14:45', '14:00'));
        $this->assertIsString($validaOrario('14:00', '14:05'));
    }
    
    /**
     * Test: Validazione data assenza
     */
    public function testValidazioneDataAssenza()
    {
        $validaDataAssenza = function($data) {
            $timestamp = strtotime($data);
            if (!$timestamp) return 'Data non valida';
            
            $oggi = strtotime(date('Y-m-d'));
            $unAnnoFa = strtotime('-1 year');
            
            if ($timestamp > $oggi) return 'Data non può essere futura';
            if ($timestamp < $unAnnoFa) return 'Data troppo vecchia';
            
            return true;
        };
        
        $this->assertTrue($validaDataAssenza(date('Y-m-d')));
        $this->assertTrue($validaDataAssenza(date('Y-m-d', strtotime('-1 month'))));
        $this->assertIsString($validaDataAssenza(date('Y-m-d', strtotime('+1 day'))));
    }
    
    /**
     * Test: Validazione durata lezione standard
     */
    public function testValidazioneDurataLezioneStandard()
    {
        $durate_valide = [30, 45, 60, 90];
        
        $isDurataValida = function($durata) use ($durate_valide) {
            return in_array($durata, $durate_valide);
        };
        
        $this->assertTrue($isDurataValida(45));
        $this->assertTrue($isDurataValida(60));
        $this->assertFalse($isDurataValida(35));
        $this->assertFalse($isDurataValida(120));
    }
    
    /**
     * Test: Validazione giorno settimana
     */
    public function testValidazioneGiornoSettimana()
    {
        $giorni_validi = ['lunedi', 'martedi', 'mercoledi', 'giovedi', 'venerdi', 'sabato'];
        
        $isGiornoValido = function($giorno) use ($giorni_validi) {
            return in_array($giorno, $giorni_validi);
        };
        
        $this->assertTrue($isGiornoValido('lunedi'));
        $this->assertTrue($isGiornoValido('sabato'));
        $this->assertFalse($isGiornoValido('domenica'));
        $this->assertFalse($isGiornoValido('invalid'));
    }
}
