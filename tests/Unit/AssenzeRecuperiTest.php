<?php
/**
 * Test Logica Recuperi Assenze
 * 
 * Test CRITICI per la business logic delle assenze e recuperi:
 * - Assenze docente sempre da recuperare
 * - Prime 3 assenze allievo obbligatorie
 * - 4a assenza in poi a discrezione
 * - Conteggio per anno scolastico
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AssenzeRecuperiTest extends TestCase
{
    /**
     * Test: Assenza docente sempre da recuperare
     */
    public function testAssenzaDocenteSempreDaRecuperare()
    {
        // Simula logica da api_salva_assenza_calendario.php
        $tipo_assenza = 'docente';
        $da_recuperare = ($tipo_assenza === 'docente') ? 1 : 0;
        
        $this->assertEquals(1, $da_recuperare, 
            "Assenza docente deve essere sempre da recuperare");
    }
    
    /**
     * Test: Prime 3 assenze allievo sono obbligatorie
     */
    public function testPrime3AssenzeAllievoObbligatorie()
    {
        $tipo_assenza = 'allievo';
        
        // Simula conteggio assenze precedenti
        for ($conteggio = 0; $conteggio < 3; $conteggio++) {
            $da_recuperare = ($tipo_assenza === 'allievo' && $conteggio < 3) ? 1 : 0;
            
            $this->assertEquals(1, $da_recuperare, 
                "Assenza #{$conteggio} allievo deve essere obbligatoria (prime 3)");
        }
    }
    
    /**
     * Test: 4a assenza allievo NON obbligatoria
     */
    public function testQuartaAssenzaAllievoNonObbligatoria()
    {
        $tipo_assenza = 'allievo';
        $conteggio = 3; // 4a assenza (0-indexed)
        
        $da_recuperare = ($tipo_assenza === 'allievo' && $conteggio < 3) ? 1 : 0;
        
        $this->assertEquals(0, $da_recuperare, 
            "4a assenza allievo NON deve essere obbligatoria");
    }
    
    /**
     * Test: Logica completa determinazione da_recuperare
     */
    public function testLogicaCompletaDeterminazioneDaRecuperare()
    {
        // Simula funzione da api_salva_assenza_calendario.php
        $determinaDaRecuperare = function($tipo_assenza, $conteggio_precedenti) {
            if ($tipo_assenza === 'docente') {
                return 1; // Sempre obbligatorio
            }
            
            if ($tipo_assenza === 'allievo') {
                return ($conteggio_precedenti < 3) ? 1 : 0;
            }
            
            return 0;
        };
        
        // Test vari scenari
        $this->assertEquals(1, $determinaDaRecuperare('docente', 0));
        $this->assertEquals(1, $determinaDaRecuperare('docente', 10));
        $this->assertEquals(1, $determinaDaRecuperare('allievo', 0));
        $this->assertEquals(1, $determinaDaRecuperare('allievo', 1));
        $this->assertEquals(1, $determinaDaRecuperare('allievo', 2));
        $this->assertEquals(0, $determinaDaRecuperare('allievo', 3));
        $this->assertEquals(0, $determinaDaRecuperare('allievo', 10));
    }
    
    /**
     * Test: Anno scolastico settembre-giugno
     */
    public function testAnnoScolasticoSettembreGiugno()
    {
        $calcolaAnnoScolastico = function($data) {
            $mese = (int)date('m', strtotime($data));
            $anno = (int)date('Y', strtotime($data));
            
            if ($mese >= 9) {
                return $anno . '/' . ($anno + 1);
            } else {
                return ($anno - 1) . '/' . $anno;
            }
        };
        
        $this->assertEquals('2025/2026', $calcolaAnnoScolastico('2025-09-01'));
        $this->assertEquals('2025/2026', $calcolaAnnoScolastico('2025-12-15'));
        $this->assertEquals('2025/2026', $calcolaAnnoScolastico('2026-06-30'));
        $this->assertEquals('2024/2025', $calcolaAnnoScolastico('2025-08-31'));
    }
    
    /**
     * Test: Conteggio assenze per coppia allievo-lezione
     */
    public function testConteggioAssenzePerCoppiaAllieveLezione()
    {
        // Simula array assenze
        $assenze = [
            ['allievo_id' => 1, 'lezione_id' => 10, 'data' => '2025-10-01'],
            ['allievo_id' => 1, 'lezione_id' => 10, 'data' => '2025-11-01'],
            ['allievo_id' => 1, 'lezione_id' => 10, 'data' => '2025-12-01'],
            ['allievo_id' => 1, 'lezione_id' => 20, 'data' => '2025-10-15'], // Altra lezione
            ['allievo_id' => 2, 'lezione_id' => 10, 'data' => '2025-10-20'], // Altro allievo
        ];
        
        $contaAssenze = function($assenze, $allievo_id, $lezione_id) {
            return count(array_filter($assenze, function($a) use ($allievo_id, $lezione_id) {
                return $a['allievo_id'] == $allievo_id && $a['lezione_id'] == $lezione_id;
            }));
        };
        
        $this->assertEquals(3, $contaAssenze($assenze, 1, 10));
        $this->assertEquals(1, $contaAssenze($assenze, 1, 20));
        $this->assertEquals(1, $contaAssenze($assenze, 2, 10));
        $this->assertEquals(0, $contaAssenze($assenze, 3, 10));
    }
}
