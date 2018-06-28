<?php

namespace BitBalm\Relator;

Interface Record 
{
    public function getTableName() : string ;
    
    public function asArray() : array ;

    public function createFromArray( array $input ) : Record ;
    
}
