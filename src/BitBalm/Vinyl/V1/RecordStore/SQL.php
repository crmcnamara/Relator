<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore;

use Doctrine\QueryBuilderQuery as Query;

use BitBlam\Vinyl\V1\RecordStore;
use BitBlam\Vinyl\V1\Record;
use BitBlam\Vinyl\V1\Collection;


interface SQL extends RecordStore
{
    public function getTable();
    public function getPrimaryKey();
    
    public function getSelectQuery() : Query ;
    public function getInsertQuery() : Query ;
    public function getUpdateQuery() : Query ;
    public function getDeleteQuery() : Query ;
    
    public function getRecordByQuery(         Query $query, array $parameters ) : Record ;
    public function getRecordsByQuery(        Query $query, array $parameters ) : Collection\Records ;
    public function getRecordByQueryString(  string $query, array $parameters ) : Record ;
    public function getRecordsByQueryString( string $query, array $parameters ) : Collection\Records ;
    
}
