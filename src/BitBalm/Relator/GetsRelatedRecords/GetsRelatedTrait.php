<?php

namespace BitBalm\Relator\GetsRelatedRecords;

use BitBalm\Relator\RecordSet;


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
