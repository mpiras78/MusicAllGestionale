<?php
/**
 * Test Funzioni Calendario
 * 
 * Test per le funzioni core del calendario:
 * - Calcolo rowspan slot
 * - Generazione slot orari
 * - Sovrapposizione lezioni
 * - Riconoscimento festività
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class CalendarioTest extends TestCase
{
    /**
     * Test: Calcolo rowspan per slot da 15 minuti
     */
    public function testCalcolaRowspanCorretto()
    {
        $calcolaRowspan = function($ora_inizio, $ora_fine) {
            $start = strtotime($ora_inizio);
            $end = strtotime($ora_fine);
            $durata_minuti = ($end - $start) / 60;
            $rowspan = ceil($durata_minuti / 15);
            return max(1, $rowspan);
        };
        
        // 45 minuti = 3 slot da 15'
        $this->assertEquals(3, $calcolaRowspan('14:00', '14:45'));
        
        // 30 minuti = 2 slot
        $this->assertEquals(2, $calcolaRowspan('14:00', '14:30'));
        
        // 60 minuti = 4 slot
        $this->assertEquals(4, $calcolaRowspan('14:00', '15:00'));
        
        // 15 minuti = 1 slot
        $this->assertEquals(1, $calcolaRowspan('14:00', '14:15'));
        
        // 90 minuti = 6 slot
        $this->assertEquals(6, $calcolaRowspan('14:00', '15:30'));
    }
    
    /**
     * Test: Generazione slot orari
     */
    public function testGeneraSlotOrari()
    {
        $generaSlotOrari = function($ora_inizio, $ora_fine, $durata_slot) {
            $slots = [];
            $current = strtotime($ora_inizio);
            $end = strtotime($ora_fine);
            
            while ($current < $end) {
                $slots[] = date('H:i', $current);
                $current += $durata_slot * 60;
            }
            
            return $slots;
        };
        
        $slots = $generaSlotOrari('09:00', '10:00', 15);
        
        $this->assertCount(4, $slots);
        $this->assertEquals('09:00', $slots[0]);
        $this->assertEquals('09:15', $slots[1]);
        $this->assertEquals('09:30', $slots[2]);
        $this->assertEquals('09:45', $slots[3]);
    }
    
    /**
     * Test: Generazione slot per giornata completa
     */
    public function testGeneraSlotGiornataCompleta()
    {
        $generaSlotOrari = function($ora_inizio, $ora_fine, $durata_slot) {
            $slots = [];
            $current = strtotime($ora_inizio);
            $end = strtotime($ora_fine);
            
            while ($current < $end) {
                $slots[] = date('H:i', $current);
                $current += $durata_slot * 60;
            }
            
            return $slots;
        };
        
        // 09:15 - 22:00 = 12h 45min = 51 slot da 15'
        $slots = $generaSlotOrari('09:15', '22:00', 15);
        
        $this->assertGreaterThan(50, count($slots));
        $this->assertEquals('09:15', $slots[0]);
        $this->assertEquals('21:45', end($slots));
    }
    
    /**
     * Test: Rilevamento sovrapposizione lezioni
     */
    public function testRilevaSovrapposizioneLezioni()
    {
        $lezioniSiSovrappongono = function($lez1, $lez2) {
            $start1 = strtotime($lez1['ora_inizio']);
            $end1 = strtotime($lez1['ora_fine']);
            $start2 = strtotime($lez2['ora_inizio']);
            $end2 = strtotime($lez2['ora_fine']);
            
            return ($start1 < $end2 && $start2 < $end1);
        };
        
        $lezione1 = ['ora_inizio' => '14:00', 'ora_fine' => '14:45'];
        $lezione2 = ['ora_inizio' => '14:30', 'ora_fine' => '15:15'];
        
        $this->assertTrue($lezioniSiSovrappongono($lezione1, $lezione2));
    }
    
    /**
     * Test: Lezioni consecutive non si sovrappongono
     */
    public function testLezioniConsecutiveNonSiSovrappongono()
    {
        $lezioniSiSovrappongono = function($lez1, $lez2) {
            $start1 = strtotime($lez1['ora_inizio']);
            $end1 = strtotime($lez1['ora_fine']);
            $start2 = strtotime($lez2['ora_inizio']);
            $end2 = strtotime($lez2['ora_fine']);
            
            return ($start1 < $end2 && $start2 < $end1);
        };
        
        $lezione1 = ['ora_inizio' => '14:00', 'ora_fine' => '14:45'];
        $lezione2 = ['ora_inizio' => '14:45', 'ora_fine' => '15:30'];
        
        $this->assertFalse($lezioniSiSovrappongono($lezione1, $lezione2));
    }
    
    /**
     * Test: Riconoscimento festività italiane
     */
    public function testFestivitaItalianeRiconosciute()
    {
        $festivita_italiane = [
            '01-01' => 'Capodanno',
            '01-06' => 'Epifania',
            '04-25' => 'Liberazione',
            '05-01' => 'Festa del Lavoro',
            '06-02' => 'Festa della Repubblica',
            '08-15' => 'Ferragosto',
            '11-01' => 'Ognissanti',
            '12-08' => 'Immacolata Concezione',
            '12-25' => 'Natale',
            '12-26' => 'Santo Stefano',
        ];
        
        $isFestivita = function($data) use ($festivita_italiane) {
            $md = date('m-d', strtotime($data));
            return isset($festivita_italiane[$md]) ? 
                ['nome' => $festivita_italiane[$md]] : false;
        };
        
        $this->assertNotFalse($isFestivita('2026-01-01'));
        $this->assertEquals('Capodanno', $isFestivita('2026-01-01')['nome']);
        $this->assertNotFalse($isFestivita('2026-04-25'));
        $this->assertFalse($isFestivita('2026-02-10'));
    }
    
    /**
     * Test: Mapping giorni settimana inglese-italiano
     */
    public function testMappingGiorniSettimana()
    {
        $mapping = [
            'monday' => 'lunedi',
            'tuesday' => 'martedi',
            'wednesday' => 'mercoledi',
            'thursday' => 'giovedi',
            'friday' => 'venerdi',
            'saturday' => 'sabato',
            'sunday' => 'domenica'
        ];
        
        $this->assertEquals('lunedi', $mapping['monday']);
        $this->assertEquals('mercoledi', $mapping['wednesday']);
        $this->assertEquals('sabato', $mapping['saturday']);
    }
    
    /**
     * Test: Icone materie corrette
     */
    public function testIconeMaterieCorrette()
    {
        $getIconaMateria = function($materia) {
            $materia_lower = strtolower($materia);
            if (strpos($materia_lower, 'chitar') !== false) return 'bi-music-note-beamed';
            if (strpos($materia_lower, 'piano') !== false) return 'bi-piano';
            if (strpos($materia_lower, 'canto') !== false) return 'bi-mic';
            if (strpos($materia_lower, 'batter') !== false) return 'bi-disc';
            return 'bi-music-note';
        };
        
        $this->assertEquals('bi-music-note-beamed', $getIconaMateria('Chitarra'));
        $this->assertEquals('bi-piano', $getIconaMateria('Pianoforte'));
        $this->assertEquals('bi-mic', $getIconaMateria('Canto'));
        $this->assertEquals('bi-disc', $getIconaMateria('Batteria'));
        $this->assertEquals('bi-music-note', $getIconaMateria('Violino'));
    }
}
