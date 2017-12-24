<?php

namespace BitBalm\Relator;

Interface Relator
{
    
    public function addRelationship( Relationship $relationship, string $name = null );
    
    public function addRelationships( array $relationships ) : Relator ; 
    
    public function getRelationship( string $fromTable, string $relationshipName ) : Relationship ;
    
    /**
     * @parameter Relationship $relationship
     *    this parameter is untyped here because implementations will likely have more stringent type requirements
     * @return RecordSet
     * 
     */
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet ;
    
}
    
