<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL;

use PDO as PDOConnection;
use PDOStatement;

use BitBalm\Vinyl\V1\RecordStore\SQL;
use BitBalm\Vinyl\V1\Record;
use BitBalm\Vinyl\V1\Collection;


interface PDO extends SQL
{
    public function getPDO() : PDOConnection ;
    
    public function getSelectStatement() : PDOStatement ;
    public function getInsertStatement() : PDOStatement ;
    public function getUpdateStatement() : PDOStatement ;
    public function getDeleteStatement() : PDOStatement ;

    public function getRecordByStatement(  PDOStatement $statement, array $parameters ) : Record ;
    public function getRecordsByStatement( PDOStatement $statement, array $parameters ) : Collection\Records ;
    
}
