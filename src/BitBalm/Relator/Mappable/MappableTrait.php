<?php

namespace BitBalm\Relator\Mappable;


use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\RecordSet;

Trait MappableTrait 
{
    protected $record_values = [];
    
    public function asArray() : array
    {
        return $this->record_values;
    }
    
    public function setValues( array $values ) : Mappable 
    {
        $this->record_values = array_replace( (array) $this->record_values, $values ) ;
        return $this;
    }
    
    public function newRecord() : Mappable
    {
        return new static;
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\Simple([ $this ]);
    }
    
}
