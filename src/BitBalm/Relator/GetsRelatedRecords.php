<?php 

namespace BitBalm\Relator;

Interface GetsRelatedRecords 
{
    
    public function getRelated( string $relationshipName ) : RecordSet ;
    
    public function getRelationship( string $relationshipName ) : Relationship ;
    
    public function getTableName() : string ;
    
    public function asRecordSet() : RecordSet ;
    
}
