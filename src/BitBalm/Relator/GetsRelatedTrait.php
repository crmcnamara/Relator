<?php

namespace BitBalm\Relator;

Trait GetsRelatedTrait 
{
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $relationship = $this->getRelationship( $relationshipName );      
        
        $related = $relationship->getToTable()->getRelator()
            ->getRelated( $relationship, $this->asRecordSet() ) ;
        
        return $related;
    }
    
}
