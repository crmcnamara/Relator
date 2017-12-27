<?php

namespace BitBalm\Relator;

Interface Record extends GetsRelatedRecords
{
    
    public function asArray() : array ;
    
    public function setRelationships( RelationshipSet $relset ) : Record ;
    
    public function setRelator( Relator $relator ) : Record ;
    
}
