<?php

namespace BitBalm\Relator;

Trait GetsRelatedTrait 
{
    
    protected $relator ;
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $relator = $this->getRelator();
        
        $related = $relator->getRelated( 
            $relator->getRelationship( $this->getTable(), $relationshipName ), 
            $this->asRecordSet() 
          ) ;
        return $related;
    }
    
    public function setRelator( Relator $relator ) : GetsRelatedRecords 
    {
        $this->relator = $relator ;
        
        return $this ;
    }
    
    public function getRelator() : Relator
    {
        return $this->relator ;
    }
    
}
