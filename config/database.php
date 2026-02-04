<?php
/**
 * Database Configuration
 * Supporto multi-database: MySQL, SQLite, PostgreSQL
 */

return [
    
    /*
    |--------------------------------------------------------------------------
    | Default Database Connection
    |--------------------------------------------------------------------------
    |
    | Specifica quale connessione usare. Può essere sovrascritto con 
    | variabile d'ambiente DB_CONNECTION
    |
    | Opzioni: 'mysql', 'sqlite', 'pgsql', 'sqlsrv', 'sqlite_file'
    |
    */
    
    'default' => getenv('DB_CONNECTION') ?: 'sqlite',
    
    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Configurazioni per diversi database. Eloquent ORM gestirà
    | automaticamente le differenze tra i vari database.
    |
    */
    
    'connections' => [
        
        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST') ?: DB_HOST,
            'port' => getenv('DB_PORT') ?: '3306',
            'database' => getenv('DB_NAME') ?: DB_NAME,
            'username' => getenv('DB_USER') ?: DB_USER,
            'password' => getenv('DB_PASS') ?: DB_PASS,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => getenv('DB_DATABASE') ?: ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        
        'sqlite_file' => [
            'driver' => 'sqlite',
            'database' => BASE_PATH . '/database/musicall.sqlite',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ],
        
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => getenv('DB_HOST') ?: 'localhost',
            'port' => getenv('DB_PORT') ?: '5432',
            'database' => getenv('DB_NAME') ?: 'musicall',
            'username' => getenv('DB_USER') ?: 'postgres',
            'password' => getenv('DB_PASS') ?: '',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | Tabella che traccia le migrations eseguite
    |
    */
    
    'migrations' => 'migrations',
    
    /*
    |--------------------------------------------------------------------------
    | Redis Databases (opzionale)
    |--------------------------------------------------------------------------
    */
    
    'redis' => [
        'client' => 'predis',
        'default' => [
            'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
            'password' => getenv('REDIS_PASSWORD') ?: null,
            'port' => getenv('REDIS_PORT') ?: 6379,
            'database' => 0,
        ],
    ],

];