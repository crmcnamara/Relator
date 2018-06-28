<?php

namespace BitBalm\Relator\Record;

use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Record;


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
    
}
