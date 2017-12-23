<?php 

namespace BitBalm\Relator;

Interface GetsRelatedRecords 
{
    
    public function getRelated( Relationship $relationship ) ;

    public function getTable() ;
    
    public function asRecordSet() : RecordSet ;
    
}
