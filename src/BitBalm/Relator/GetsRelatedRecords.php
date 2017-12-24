<?php 

namespace BitBalm\Relator;

Interface GetsRelatedRecords 
{
    
    public function getRelated( string $relationshipName ) : RecordSet ;

    public function getTable() : string ;
    
    public function getRelator() : Relator ;
    
    public function asRecordSet() : RecordSet ;
    
}
