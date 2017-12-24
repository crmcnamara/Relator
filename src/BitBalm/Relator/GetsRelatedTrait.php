<?php

namespace BitBalm\Relator;

Trait GetsRelatedTrait 
{
    
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $relator = $this->getRelator();
        
        $related = $relator->getRelated( 
            $relator->getRelationship( $this->getTable(), $relationshipName ), 
            $this->asRecordSet() 
          ) ;
        return $related;
    }
    

    
}
