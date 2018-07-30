<?php

namespace BitBalm\Relator;


interface Relatable extends Mappable
{
    public function getTableName() /*: string*/ ;
    
    public function setRelator( Relator $relator ) /*: Relatable*/ ;
    
    public function getRelator() /*: Relator*/ ;
    
    public function asRecordSet() /*: RecordSet*/ ;
}
