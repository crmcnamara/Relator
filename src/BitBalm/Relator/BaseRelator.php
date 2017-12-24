<?php

namespace BitBalm\Relator;

use InvalidArgumentException;

# TODO: trait to handle relationship registration?
Abstract class BaseRelator implements Relator
{
    
    protected $relationships = [] ;
    
    public function addRelationship( Relationship $relationship, string $name = null ) : Relator
    {
        if ( ! ( $fromTable = $relationship->getFromTable()->getTable() ) ) {
            throw new InvalidArgumentException("Relationship's 'from' table is undefined. ");
        }
        
        if ( empty($name) ) { $name = $relationship->getToTable()->getTable(); }
                
        $this->relationships[$fromTable][$name] = $relationship;
        
        $relationship->setRelator($this);        
        $relationship->getFromTable()->setRelator($this);
        $relationship->getToTable()->setRelator($this);
        
        return $this;
    }
    
    public function addRelationships( array $relationships ) : Relator
    {
        foreach ( $relationships as $name => $relationship ) { 
            $this->addRelationship(
                $relationship, 
                is_string($name) ? $name : null 
              ); 
        }
        return $this;      
    }
    
    public function getRelationship( string $fromTable, string $relationshipName ) : Relationship
    {
        return $this->relationships[$fromTable][$relationshipName] ?? null ;
    }
    
}
