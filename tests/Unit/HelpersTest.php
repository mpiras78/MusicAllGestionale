<?php
/**
 * Test Funzioni Helper
 * 
 * Test per utility e helper functions:
 * - Formattazione date
 * - Validazione input
 * - Sanitizzazione
 * - Utility varie
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    /**
     * Test: Formattazione data italiana
     */
    public function testFormattaDataItaliana()
    {
        $formattaData = function($data, $formato = 'd/m/Y') {
            return date($formato, strtotime($data));
        };
        
        $this->assertEquals('16/02/2026', $formattaData('2026-02-16'));
        $this->assertEquals('01/01/2026', $formattaData('2026-01-01'));
        $this->assertEquals('31/12/2025', $formattaData('2025-12-31'));
    }
    
    /**
     * Test: Formattazione ora
     */
    public function testFormattaOra()
    {
        $formattaOra = function($ora) {
            return substr($ora, 0, 5); // HH:MM
        };
        
        $this->assertEquals('14:30', $formattaOra('14:30:00'));
        $this->assertEquals('09:15', $formattaOra('09:15:00'));
        $this->assertEquals('22:00', $formattaOra('22:00:00'));
    }
    
    /**
     * Test: Sanitizzazione input (XSS prevention)
     */
    public function testSanitizzaInput()
    {
        $sanitize = function($input) {
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        };
        
        $input = "<script>alert('xss')</script>Mario";
        $safe = $sanitize($input);
        
        $this->assertStringNotContainsString('<script>', $safe);
        $this->assertStringContainsString('Mario', $safe);
        $this->assertStringContainsString('&lt;script&gt;', $safe);
    }
    
    /**
     * Test: Validazione email
     */
    public function testValidazioneEmail()
    {
        $isValidEmail = function($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
        };
        
        $this->assertTrue($isValidEmail('test@example.com'));
        $this->assertTrue($isValidEmail('mario.rossi@musicall.it'));
        $this->assertFalse($isValidEmail('invalid-email'));
        $this->assertFalse($isValidEmail('test@'));
        $this->assertFalse($isValidEmail('@example.com'));
    }
    
    /**
     * Test: Validazione telefono italiano
     */
    public function testValidazioneTelefonoItaliano()
    {
        $isValidTelefono = function($telefono) {
            $cleaned = preg_replace('/[^0-9]/', '', $telefono);
            return strlen($cleaned) >= 9 && strlen($cleaned) <= 13;
        };
        
        $this->assertTrue($isValidTelefono('3331234567'));
        $this->assertTrue($isValidTelefono('333 123 4567'));
        $this->assertTrue($isValidTelefono('+39 333 1234567'));
        $this->assertTrue($isValidTelefono('02 12345678'));
        $this->assertFalse($isValidTelefono('12345'));
        $this->assertFalse($isValidTelefono('abc'));
    }
    
    /**
     * Test: Generazione slug da testo
     */
    public function testGenerazioneSlug()
    {
        $generateSlug = function($text) {
            $text = strtolower($text);
            $text = preg_replace('/[^a-z0-9]+/', '-', $text);
            return trim($text, '-');
        };
        
        $this->assertEquals('mario-rossi', $generateSlug('Mario Rossi'));
        $this->assertEquals('lezione-di-piano', $generateSlug('Lezione di Piano'));
        $this->assertEquals('test-123', $generateSlug('Test 123'));
    }
    
    /**
     * Test: Calcolo età da data nascita
     */
    public function testCalcoloEta()
    {
        $calcolaEta = function($data_nascita) {
            $oggi = new \DateTime();
            $nascita = new \DateTime($data_nascita);
            return $oggi->diff($nascita)->y;
        };
        
        $eta = $calcolaEta('2010-01-01');
        $this->assertGreaterThanOrEqual(14, $eta);
        $this->assertLessThanOrEqual(16, $eta);
    }
    
    /**
     * Test: Formattazione valuta euro
     */
    public function testFormattazioneValutaEuro()
    {
        $formattaEuro = function($importo) {
            return '€ ' . number_format($importo, 2, ',', '.');
        };
        
        $this->assertEquals('€ 50,00', $formattaEuro(50));
        $this->assertEquals('€ 1.250,50', $formattaEuro(1250.50));
        $this->assertEquals('€ 0,99', $formattaEuro(0.99));
    }
    
    /**
     * Test: Troncamento testo con ellipsis
     */
    public function testTroncamentoTesto()
    {
        $truncate = function($text, $length = 50) {
            if (strlen($text) <= $length) return $text;
            return substr($text, 0, $length) . '...';
        };
        
        $lungo = 'Questo è un testo molto lungo che deve essere troncato';
        $corto = 'Testo breve';
        
        $this->assertStringEndsWith('...', $truncate($lungo, 20));
        $this->assertEquals($corto, $truncate($corto, 50));
        $this->assertLessThanOrEqual(23, strlen($truncate($lungo, 20)));
    }
    
    /**
     * Test: Generazione colore da stringa (per avatar)
     */
    public function testGenerazioneColoreAvatar()
    {
        $stringToColor = function($string) {
            $hash = md5($string);
            return '#' . substr($hash, 0, 6);
        };
        
        $color1 = $stringToColor('Mario Rossi');
        $color2 = $stringToColor('Laura Bianchi');
        
        $this->assertStringStartsWith('#', $color1);
        $this->assertEquals(7, strlen($color1));
        $this->assertNotEquals($color1, $color2);
    }
    
    /**
     * Test: Estrazione iniziali nome
     */
    public function testEstrazioneInizialiNome()
    {
        $getInitials = function($nome, $cognome) {
            return strtoupper(substr($nome, 0, 1) . substr($cognome, 0, 1));
        };
        
        $this->assertEquals('MR', $getInitials('Mario', 'Rossi'));
        $this->assertEquals('LB', $getInitials('Laura', 'Bianchi'));
        $this->assertEquals('GP', $getInitials('Giovanni', 'Paoli'));
    }
}
