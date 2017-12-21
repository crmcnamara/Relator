<?php

namespace BitBalm\Relator;

Interface Relator
{
    
    /**
     * @parameter Relationship $relationship
     * @parameter string $name (optional)
     */
    public function addRelationship( Relationship $relationship, $name = null );
    
    public function addRelationships( array $relationships ); 
    
    /**
     * @parameter Relationship $relationship
     *    this parameter is untyped here because implementations will likely have more stringent type requirements
     * @return RecordSet
     * 
     */
    public function getRelated( Relationship $relationship, RecordSet $recordset );
    
}
    
