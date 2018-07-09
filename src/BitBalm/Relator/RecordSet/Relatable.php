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


    protected function validRecord( Record $record ) : Record 
    {
        return parent::validRecord($this->validRelatable($record));
    }
    
    protected function validRelatable( RelatableRecord $record ) : RelatableRecord 
    {
        return $record;
    }
    
    protected function getRecord() : RelatableRecord
    {
        if ( empty($this->record) ) { 
            throw new Exception("This RecordSet's Record type is not yet set. ");
        }
        return $this->record;
    }
    
    public function getTableName() : string
    {
        return $this->getRecord()->getTableName() ; 
    }
    
    public function getRelationship( string $relationshipName ) : Relationship
    {
        return $this->getRecord()->getRelationship($relationshipName);
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
}
