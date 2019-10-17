<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\SQL\PDO;

use PDO;


class MySQL extends PDO
{
    public $database;
    protected $dsn = 'mysql:';
    protected $database_id;
    
    
    public function __construct()
    {
        $database_id = bin2hex(random_bytes(8));
        $this->database = 'vinyl_test_'. $database_id;
        $this->dsn = "mysql:dbname={$this->database}";
        
        // use a separate connection to drop and create a new database using the random db id
        $pre_pdo = new PDO( 'mysql:', 'vinyl', null, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ] );
        $pre_pdo->exec("drop database if exists {$this->database} ");
        $pre_pdo->exec("create database {$this->database} ");
        $pre_pdo = null;
        
        parent::__construct( $this->dsn, 'vinyl', null, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ] );
    }
    
    public function getDatabase() : string
    {
        return $this->database;
    }
    
    public function __destruct()
    {
        $this->exec("drop database if exists {$this->database} ");
    }
    
}
