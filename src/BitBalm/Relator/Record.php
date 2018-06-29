<?php

namespace BitBalm\Relator;

Interface Record 
{
    public function getTableName() : string ;
    
    public function newRecord() : Record ;
    
    public function setValues( array $values ) : Record ;
    
    public function asArray() : array ;
}
