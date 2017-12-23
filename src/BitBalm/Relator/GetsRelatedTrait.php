<?php

namespace BitBalm\Relator;

Trait GetsRelatedTrait 
{
    
    public function getRelated( Relationship $relationship ) 
    {
        return $relationship->getRelator()->getRelated( $relationship, $this->asRecordSet() ) ;
    }
    
}
