<?php

namespace BitBalm\Relator;

Trait RecordTrait 
{
    
    public function getRelated( Relationship $relationship ) 
    {
        
        $recordset = ( $this instanceof Record ) ? $this->asRecordSet() : $this ;            
        
        return $relationship->getRelator()->getRelated( $relationship, $recordset ) ;
    }
    
}
