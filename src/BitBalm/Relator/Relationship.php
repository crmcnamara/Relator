<?php

namespace BitBalm\Relator;

Interface Relationship
{
    
    /**
     * @return Relator
     */
    public function getRelator();
    
    public function setRelator( Relator $relator );
    
}
