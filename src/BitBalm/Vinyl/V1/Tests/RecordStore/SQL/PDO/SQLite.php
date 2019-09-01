<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore\SQL\PDO;

use PDO;


class SQLite extends PDO
{
    public $dsn = 'sqlite::memory:';
    
    
    public function __construct()
    {
        parent::__construct( $this->dsn, null, null, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ] );
    }
}
