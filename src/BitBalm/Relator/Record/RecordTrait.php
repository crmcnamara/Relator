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
    
    public function createFromArray( array $values ) : Record
    {
        $record = new static;
        $record->record_values = $values ;
        return $record;
    }

    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset( [ $this ] ) : new RecordSet\Simple( [ $this ] );
    }
    
}
