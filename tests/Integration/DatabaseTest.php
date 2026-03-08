<?php
/**
 * Test Integrazione Database
 * 
 * Test per verificare:
 * - Connessione database
 * - Query complesse
 * - Transazioni
 * - Viste
 */

namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * Test: Connessione database funzionante
     */
    public function testConnessioneDatabaseFunziona()
    {
        // Simula connessione PDO
        $testConnection = function() {
            try {
                $pdo = new \PDO('sqlite::memory:');
                $result = $pdo->query('SELECT 1 as test')->fetch();
                return $result['test'] === 1;
            } catch (\Exception $e) {
                return false;
            }
        };
        
        $this->assertTrue($testConnection());
    }
    
    /**
     * Test: Creazione tabella e insert
     */
    public function testCreazioneTabella()
    {
        $pdo = new \PDO('sqlite::memory:');
        
        $pdo->exec('CREATE TABLE test_allievi (
            id INTEGER PRIMARY KEY,
            nome TEXT NOT NULL,
            cognome TEXT NOT NULL
        )');
        
        $stmt = $pdo->prepare('INSERT INTO test_allievi (nome, cognome) VALUES (?, ?)');
        $stmt->execute(['Mario', 'Rossi']);
        
        $result = $pdo->query('SELECT COUNT(*) as count FROM test_allievi')->fetch();
        
        $this->assertEquals(1, $result['count']);
    }
    
    /**
     * Test: Transazione con commit
     */
    public function testTransazioneCommit()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE test_data (id INTEGER PRIMARY KEY, value TEXT)');
        
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO test_data (value) VALUES ('test1')");
        $pdo->exec("INSERT INTO test_data (value) VALUES ('test2')");
        $pdo->commit();
        
        $count = $pdo->query('SELECT COUNT(*) as count FROM test_data')->fetch();
        
        $this->assertEquals(2, $count['count']);
    }
    
    /**
     * Test: Transazione con rollback
     */
    public function testTransazioneRollback()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE test_data (id INTEGER PRIMARY KEY, value TEXT)');
        
        $pdo->beginTransaction();
        $pdo->exec("INSERT INTO test_data (value) VALUES ('test1')");
        $pdo->rollBack();
        
        $count = $pdo->query('SELECT COUNT(*) as count FROM test_data')->fetch();
        
        $this->assertEquals(0, $count['count']);
    }
    
    /**
     * Test: Query con JOIN
     */
    public function testQueryConJoin()
    {
        $pdo = new \PDO('sqlite::memory:');
        
        // Updated to use `soci` and `socio_id` (migration allievi -> soci)
        $pdo->exec('CREATE TABLE soci (id INTEGER PRIMARY KEY, nome TEXT)');
        $pdo->exec('CREATE TABLE lezioni (id INTEGER PRIMARY KEY, socio_id INTEGER, materia TEXT)');

        $pdo->exec("INSERT INTO soci (id, nome) VALUES (1, 'Mario')");
        $pdo->exec("INSERT INTO lezioni (socio_id, materia) VALUES (1, 'Piano')");

        $result = $pdo->query('
            SELECT a.nome, l.materia 
            FROM soci a 
            JOIN lezioni l ON a.id = l.socio_id
        ')->fetch();
        
        $this->assertEquals('Mario', $result['nome']);
        $this->assertEquals('Piano', $result['materia']);
    }
    
    /**
     * Test: Query con aggregazione
     */
    public function testQueryConAggregazione()
    {
        $pdo = new \PDO('sqlite::memory:');
        
        // Use socio_id for consistency with migrated schema
        $pdo->exec('CREATE TABLE assenze (id INTEGER PRIMARY KEY, socio_id INTEGER)');
        $pdo->exec("INSERT INTO assenze (socio_id) VALUES (1), (1), (2)");

        $result = $pdo->query('
            SELECT socio_id, COUNT(*) as totale 
            FROM assenze 
            GROUP BY socio_id
        ')->fetchAll();
        
        $this->assertCount(2, $result);
        $this->assertEquals(2, $result[0]['totale']);
    }
    
    /**
     * Test: Prepared statement con parametri
     */
    public function testPreparedStatement()
    {
        $pdo = new \PDO('sqlite::memory:');
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, email TEXT UNIQUE)');
        
        $stmt = $pdo->prepare('INSERT INTO users (email) VALUES (?)');
        $stmt->execute(['test@example.com']);
        
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute(['test@example.com']);
        $result = $stmt->fetch();
        
        $this->assertEquals('test@example.com', $result['email']);
    }
}
