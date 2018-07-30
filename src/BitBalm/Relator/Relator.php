<?php

namespace BitBalm\Relator;

Interface Relator
{
    
    public function getRelated( GetsRelatedRecords $related_from, Relationship $relationship ) /*: RecordSet*/ ;
    
}
    
