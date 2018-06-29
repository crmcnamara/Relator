<?php

namespace BitBalm\Relator;


interface Relatable extends Record
{
    public function getTableName() : string ;
    
    public function setRelator( Relator $relator ) : Record ;
    
    public function getRelator() : Relator ;
      
    public function setRelationship( Relationship $relationship, string $relationshipName = null ) : Record ;
}
