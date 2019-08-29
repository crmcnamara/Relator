<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL;

use PDO as PDOConnection;
use PDOStatement;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record;
use BitBalm\Vinyl\V1\Collection;


interface PDO extends Vinyl\RecordStore\SQL
{
    public function getPDO() : PDOConnection ;
    
    public function getRecordByStatement(  PDOStatement $statement, array $parameters ) : Record ;
    public function getRecordsByStatement( PDOStatement $statement, array $parameters ) : Collection\Records ;
    
}
