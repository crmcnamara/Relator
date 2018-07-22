<?php 

namespace BitBalm\Relator\RecordSet;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Relatable as RelatableRecord;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

use ArrayObject;
use InvalidArgumentException;


class GetsRelated extends RecordSet\Mappable implements RecordSet, GetsRelatedRecords
{
    use GetsRelatedTrait;


    protected function validRecord( Mappable $record ) : Mappable
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
    
    public function setRelationship( Relationship $relationship, string $relationship_name = null ) : GetsRelatedRecords 
    {
        return $this->getRecord()->setRelationship( $relationship, $relationship_name );
    }
    
    public function getRelationship( string $relationship_name ) : Relationship
    {
        return $this->getRecord()->getRelationship($relationship_name);
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
}
