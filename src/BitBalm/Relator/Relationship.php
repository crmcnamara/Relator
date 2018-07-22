<?php

namespace BitBalm\Relator;

Interface Relationship
{
    public function getFromTable()    : Mappable ;
    public function getFromColumn()   : string ; 
    public function getToTable()      : Relatable ;
    public function getToColumn()     : string ;
    
}
