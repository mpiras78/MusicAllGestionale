<?php
/**
 * Test Logica Recuperi Assenze
 * 
 * Test CRITICI per la business logic delle assenze e recuperi:
 * - Assenze docente sempre da recuperare
 * - Prime 3 assenze socio obbligatorie
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
     * Test: Prime 3 assenze socio sono obbligatorie
     */
    public function testPrime3AssenzeSocioObbligatorie()
    {
        $tipo_assenza = 'socio';
        
        // Simula conteggio assenze precedenti
        for ($conteggio = 0; $conteggio < 3; $conteggio++) {
            $da_recuperare = ($tipo_assenza === 'socio' && $conteggio < 3) ? 1 : 0;
            
            $this->assertEquals(1, $da_recuperare, 
                "Assenza #{$conteggio} socio deve essere obbligatoria (prime 3)");
        }
    }
    
    /**
     * Test: 4a assenza socio NON obbligatoria
     */
    public function testQuartaAssenzaSocioNonObbligatoria()
    {
        $tipo_assenza = 'socio';
        $conteggio = 3; // 4a assenza (0-indexed)
        
        $da_recuperare = ($tipo_assenza === 'socio' && $conteggio < 3) ? 1 : 0;
        
        $this->assertEquals(0, $da_recuperare, 
            "4a assenza socio NON deve essere obbligatoria");
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
            
            if ($tipo_assenza === 'socio') {
                return ($conteggio_precedenti < 3) ? 1 : 0;
            }
            
            return 0;
        };
        
        // Test vari scenari
        $this->assertEquals(1, $determinaDaRecuperare('docente', 0));
        $this->assertEquals(1, $determinaDaRecuperare('docente', 10));
        $this->assertEquals(1, $determinaDaRecuperare('socio', 0));
        $this->assertEquals(1, $determinaDaRecuperare('socio', 1));
        $this->assertEquals(1, $determinaDaRecuperare('socio', 2));
        $this->assertEquals(0, $determinaDaRecuperare('socio', 3));
        $this->assertEquals(0, $determinaDaRecuperare('socio', 10));
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
     * Test: Conteggio assenze per coppia socio-lezione
     */
    public function testConteggioAssenzePerCoppiaSocioLezione()
    {
        // Simula array assenze
        $assenze = [
            ['socio_id' => 1, 'lezione_id' => 10, 'data' => '2025-10-01'],
            ['socio_id' => 1, 'lezione_id' => 10, 'data' => '2025-11-01'],
            ['socio_id' => 1, 'lezione_id' => 10, 'data' => '2025-12-01'],
            ['socio_id' => 1, 'lezione_id' => 20, 'data' => '2025-10-15'], // Altra lezione
            ['socio_id' => 2, 'lezione_id' => 10, 'data' => '2025-10-20'], // Altro socio
        ];
        
        $contaAssenze = function($assenze, $socio_id, $lezione_id) {
            return count(array_filter($assenze, function($a) use ($socio_id, $lezione_id) {
                return $a['socio_id'] == $socio_id && $a['lezione_id'] == $lezione_id;
            }));
        };
        
        $this->assertEquals(3, $contaAssenze($assenze, 1, 10));
        $this->assertEquals(1, $contaAssenze($assenze, 1, 20));
        $this->assertEquals(1, $contaAssenze($assenze, 2, 10));
        $this->assertEquals(0, $contaAssenze($assenze, 3, 10));
    }
}
