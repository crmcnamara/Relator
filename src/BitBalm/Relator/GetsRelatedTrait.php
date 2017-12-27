<?php

namespace BitBalm\Relator;

Trait GetsRelatedTrait 
{
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $related = $this->getRelator()->getRelated( 
            $this->getRelationship( $relationshipName ),
            $this->asRecordSet() 
          ) ;
        return $related;
    }
    
}
