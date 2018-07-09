<?php 

namespace BitBalm\Relator;

Interface GetsRelatedRecords 
{
    
    public function getRelated( string $relationship_name ) : RecordSet ;
    
    public function getRelationship( string $relationship_name ) : Relationship ;

    public function asRecordSet() : RecordSet ;
    
}
