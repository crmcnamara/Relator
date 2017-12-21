<?php 

namespace BitBalm\Relator;

Abstract class BaseRelationship
{
    
    protected $relator ;
    
    /**
     * @return Relator
     */
    public function getRelator() 
    {
        return $this->relator;
    }
    
    public function setRelator( Relator $relator )
    {
        $this->relator = $relator;
        return $this;
    }
    
    
}
