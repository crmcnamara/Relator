<?php

namespace BitBalm\Relator;

Trait RecordTrait 
{
    use GetsRelatedTrait;
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([$this]) : new SimpleRecordSet([ $this ]);
    }
    
}
