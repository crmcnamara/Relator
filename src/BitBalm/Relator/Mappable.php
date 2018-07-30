<?php

namespace BitBalm\Relator;

Interface Mappable 
{
    public function getTableName() /*: string*/ ;
    
    public function newRecord() /*: Mappable*/ ;
    
    public function setValues( array $values ) /*: Mappable*/ ;
    
    public function asArray() /*: array*/ ;
}
