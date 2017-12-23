<?php

namespace BitBalm\Relator;

Interface Record extends GetsRelatedRecords
{
    
    /**
     * @return RecordSet
     */
    public function asRecordSet() : RecordSet ;
    
    /**
     * @return associative array of the record's values
     */
    public function asArray() : array ;
    
}
