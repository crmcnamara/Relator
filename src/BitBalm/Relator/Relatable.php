<?php

namespace BitBalm\Relator;


interface Relatable extends Mappable
{
    public function getTableName() : string ;
    
    public function setRelator( Relator $relator ) : Mappable ;
    
    public function getRelator() : Relator ;
      
    public function setRelationship( Relationship $relationship, string $relationshipName = null ) : Mappable ;
}
