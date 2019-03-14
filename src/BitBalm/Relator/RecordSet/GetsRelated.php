<?php 

namespace BitBalm\Relator\RecordSet;

use ArrayObject;
use InvalidArgumentException;
use RuntimeException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Relatable as RelatableRecord;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;
use BitBalm\Relator\Exception\RecordNotYetSet;


class GetsRelated extends RecordSet\Mappable implements RecordSet, GetsRelatedRecords
{
    use GetsRelatedTrait;


    public function __construct( array $records, RelatableRecord $record_type, $flags = null )
    {
        parent::__construct( $records, $record_type, $flags );
        
    }

    protected function validRecord( Mappable $record ) : Mappable
    {
        return parent::validRecord($this->validRelatable($record));
    }
    
    protected function validRelatable( RelatableRecord $record ) : RelatableRecord 
    {
        return $record;
    }
    
    public function getTableName() : string
    {
        return $this->getRecordType()->getTableName() ; 
    }
    
    public function setRelationship( Relationship $relationship, string $relationship_name = null ) : GetsRelatedRecords 
    {
        return $this->getRecordType()->setRelationship( $relationship, $relationship_name );
    }
    
    public function getRelationship( string $relationship_name ) : Relationship
    {
        return $this->getRecordType()->getRelationship($relationship_name);
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
}
