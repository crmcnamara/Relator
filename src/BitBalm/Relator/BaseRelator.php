<?php

namespace BitBalm\Relator;


# TODO: trait to handle relationship registration?
Abstract class BaseRelator implements Relator
{
    
    protected $relationships = [] ;
    
    public function addRelationship( Relationship $relationship, string $name = null )
    {
        $name = strval( $name ) ;
        
        #TODO: how do we want to index/look these up?
        
        if ( ! empty($name) ) {
            $this->relationships[$name] = $relationship ;
        } else {
            $this->relationships[] = $relationship;
        }
        
        $relationship->setRelator($this);
        
        return $this;
    }
    
    public function addRelationships( array $relationships ) 
    {
        foreach ( $relationships as $relationship ) { $this->addRelationship($relationship); }
        return $this;      
    }
    
}
