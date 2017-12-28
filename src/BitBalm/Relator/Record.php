<?php

namespace BitBalm\Relator;

Interface Record extends GetsRelatedRecords
{
    
    public function asArray() : array ;
    
    public function setRelator( Relator $relator ) : Record ;
    
    public function addRelationship( 
        string $fromColumn, 
        Record $toTable, 
        string $toColumn, 
        string $relationshipName = null 
      ) : Record ;
      
    public function setRelationship( Relationship $relationship, string $relationshipName = null ) : Record ;
    
    
    
}
