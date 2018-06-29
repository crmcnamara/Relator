<?php

namespace BitBalm\Relator\Record;


use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Record;
use BitBalm\Relator\RecordSet;

Trait RecordTrait 
{
    protected $record_values = [];
    
    public function asArray() : array
    {
        return $this->record_values;
    }
    
    public function setValues( array $values ) : Record 
    {
        $this->record_values = array_replace( (array) $this->record_values, $values ) ;
        return $this;
    }
    
    public function newRecord() : Record
    {
        return new static;        
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\Simple([ $this ]);
    }
    
}
