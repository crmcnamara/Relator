<?php

namespace BitBalm\Relator;

Interface Relationship
{
    
    public function getFromTable()    : Record ;
    public function getFromColumn()   : string ; 
    public function getToTable()      : Record ;
    public function getToColumn()     : string ;
    
}
