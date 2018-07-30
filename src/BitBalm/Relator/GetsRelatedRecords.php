<?php 

namespace BitBalm\Relator;


Interface GetsRelatedRecords 
{
    public function setRelationship( Relationship $relationship, /*string*/ $relationship_name = null ) /*: GetsRelatedRecords*/ ;
    
    public function getRelationship( /*string*/ $relationship_name ) /*: Relationship*/ ;
    
    public function getRelated( /*string*/ $relationship_name ) /*: RecordSet*/ ;

    public function asRecordSet() /*: RecordSet*/ ;
    
}
