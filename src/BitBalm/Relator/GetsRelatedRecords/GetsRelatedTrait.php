<?php

namespace BitBalm\Relator\GetsRelatedRecords;

use BitBalm\Relator\RecordSet;


Trait GetsRelatedTrait 
{
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $relationship = $this->getRelationship( $relationshipName );
        
        $related = $relationship->getToTable()->getRelator()
            ->getRelated( $relationship, $this->asRecordSet() ) ;
        
        return $related;
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset( [ $this ] ) : new RecordSet\Relatable( [ $this ] );
    }
}
