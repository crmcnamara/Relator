<?php

namespace BitBalm\Relator;

Interface Relator
{
    
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet ;
    
}
    
