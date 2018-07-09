<?php 

namespace BitBalm\Relator\RecordSet;

use BitBalm\Relator\Record;
use BitBalm\Relator\Relatable as RelatableRecord;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

use ArrayObject;
use InvalidArgumentException;


class Relatable extends Simple implements RecordSet, GetsRelatedRecords
{
    
    use GetsRelatedTrait;


    protected function validateRecords( Record ...$records ) : RecordSet 
    {
        return $this->validateRelatables( ...$records );
    }
    
    protected function validateRelatables( RelatableRecord ...$records ) : RecordSet\Relatable 
    {
        return $this;
    }
    
    public function getTableName() : string
    {
        if ( $this->record instanceof RelatableRecord ) { return $this->record->getTableName() ; }
        throw new Exception("This RecordSet's Record type is not yet set. ");
    }
    
    public function getRelationship( string $relationshipName ) : Relationship
    {
        if ( $this->record instanceof RelatableRecord ) { return $this->record->getRelationship( $relationshipName ); }
        throw new Exception("This RecordSet's Record type is not yet set. ");
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
}
