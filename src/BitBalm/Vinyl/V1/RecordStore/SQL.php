<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore;

use BitBalm\Vinyl\V1\RecordStore;
use BitBalm\Vinyl\V1\Record;
use BitBalm\Vinyl\V1\Collection;


interface SQL extends RecordStore
{
    public function getTable();
    public function getPrimaryKey();
    
    public function getRecordByQueryString(  string $query, array $parameters ) : Record ;
    public function getRecordsByQueryString( string $query, array $parameters ) : Vinyl\RecordProducer ;
    
}
