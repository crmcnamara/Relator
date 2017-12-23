<?php

namespace BitBalm\Relator;

Trait RecordTrait 
{
    
    abstract public function getTable() ;
    
    public function getRelated( Relationship $relationship ) 
    {
        $recordset = ( $this instanceof Record ) ? $this->asRecordSet() : $this ;            
        
        return $relationship->getRelator()->getRelated( $relationship, $recordset ) ;
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([$this]) : new SimpleRecordSet([ $this ]);
    }
    
}
