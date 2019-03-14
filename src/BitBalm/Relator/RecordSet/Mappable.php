<?php 

namespace BitBalm\Relator\RecordSet;

use ArrayObject;

use BitBalm\Relator\Mappable as MappableRecord;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;
use BitBalm\Relator\Exception\InvalidRecord;


class Mappable extends ArrayObject implements RecordSet 
{
    protected $record ;
    
    public function __construct( array $records, MappableRecord $record_type, $flags = null )
    {
        $this->record = $record_type;
        
        foreach ( $records as $record ) { $this->validRecord($record); }
        parent::__construct( $records, $flags );
        
    }

    protected function validRecord( MappableRecord $record ) /*: MappableRecord*/
    {
        $record_type = $this->getRecordType();
        if ( ! $record instanceof $record_type ) {
            throw new InvalidRecord( 
                'Passed record of class '. get_class($record)
                .' must be an instance of class '. get_class($record_type) .'. '
              );
        }
        return $record;
    }
    
    public function getRecordType() /*: MappableRecord*/
    {
        return $this->record;
    }
    
    public  function offsetSet( $key, $value ) 
    {
        parent::offsetSet( $key, $this->validRecord($value) );
    }
    
    public function asArrays() /*: array*/ 
    {
        return array_map( function($record) { return $record->asArray(); }, (array) $this );
    }
    
    
    
}
