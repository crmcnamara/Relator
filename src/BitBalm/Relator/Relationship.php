<?php

namespace BitBalm\Relator;

Interface Relationship
{
    
    public function getRelator() : Relator ;
    
    public function setRelator( Relator $relator ) : Relationship ;
    
    public function getFromTable()    : Record ;
    public function getFromColumn()   : string ; 
    public function getToTable()      : Record ;
    public function getToColumn()     : string ;
    
}
