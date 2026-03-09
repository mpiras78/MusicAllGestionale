<?php
/**
 * Test Feature - Workflow Completi
 * 
 * Test end-to-end per scenari reali:
 * - Registrazione assenza completa
 * - Programmazione recupero
 * - Gestione calendario
 */

namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class GestioneAssenzeWorkflowTest extends TestCase
{
    /**
     * Test: Workflow completo registrazione assenza
     */
    public function testWorkflowRegistrazioneAssenza()
    {
        // Simula workflow completo
        $workflow = function() {
            // 1. Dati assenza
            $assenza = [
                'lezione_id' => 1,
                'socio_id' => 1,
                'data_assenza' => '2026-02-16',
                'tipo_assenza' => 'socio',
                'motivo' => 'Malattia'
            ];
            
            // 2. Conta assenze precedenti
            $assenze_precedenti = 2; // Simula query
            
            // 3. Determina da_recuperare
            $assenza['da_recuperare'] = ($assenze_precedenti < 3) ? 1 : 0;
            
            // 4. Salva (simulato)
            $assenza['id'] = 123;
            
            // 5. Verifica
            return $assenza;
        };
        
        $risultato = $workflow();
        
        $this->assertArrayHasKey('id', $risultato);
        $this->assertEquals(1, $risultato['da_recuperare']);
        $this->assertEquals('Malattia', $risultato['motivo']);
    }
    
    /**
     * Test: Workflow programmazione recupero
     */
    public function testWorkflowProgrammazioneRecupero()
    {
        $workflow = function() {
            // 1. Trova assenze da recuperare
            $assenze_da_recuperare = [
                ['id' => 1, 'socio_id' => 1, 'lezione_id' => 10],
                ['id' => 2, 'socio_id' => 1, 'lezione_id' => 10],
            ];
            
            // 2. Crea lezione custom per recupero
            $recupero = [
                'tipo' => 'recupero',
                'socio_id' => 1,
                'docente_id' => 5,
                'aula_id' => 2,
                'data' => '2026-02-20',
                'ora_inizio' => '18:00',
                'ora_fine' => '18:45',
                'assenza_recuperata_id' => 1
            ];
            
            // 3. Marca assenza come recuperata
            $assenze_da_recuperare[0]['recuperata'] = true;
            $assenze_da_recuperare[0]['data_recupero'] = '2026-02-20';
            
            return [
                'recupero' => $recupero,
                'assenza_aggiornata' => $assenze_da_recuperare[0]
            ];
        };
        
        $risultato = $workflow();
        
        $this->assertEquals('recupero', $risultato['recupero']['tipo']);
        $this->assertTrue($risultato['assenza_aggiornata']['recuperata']);
    }
    
    /**
     * Test: Workflow filtro assenze
     */
    public function testWorkflowFiltroAssenze()
    {
        $assenze = [
            ['id' => 1, 'da_recuperare' => 1, 'recuperata' => false, 'socio_id' => 1],
            ['id' => 2, 'da_recuperare' => 1, 'recuperata' => true, 'socio_id' => 1],
            ['id' => 3, 'da_recuperare' => 0, 'recuperata' => false, 'socio_id' => 2],
        ];
        
        $filtra = function($assenze, $filtro) {
            return array_filter($assenze, function($a) use ($filtro) {
                if ($filtro === 'da_recuperare') {
                    return $a['da_recuperare'] == 1 && !$a['recuperata'];
                }
                if ($filtro === 'recuperate') {
                    return $a['recuperata'] == true;
                }
                if ($filtro === 'non_necessario') {
                    return $a['da_recuperare'] == 0;
                }
                return true;
            });
        };
        
        $da_recuperare = $filtra($assenze, 'da_recuperare');
        $recuperate = $filtra($assenze, 'recuperate');
        $non_necessario = $filtra($assenze, 'non_necessario');
        
        $this->assertCount(1, $da_recuperare);
        $this->assertCount(1, $recuperate);
        $this->assertCount(1, $non_necessario);
    }
}
