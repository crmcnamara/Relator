<?php 

namespace BitBalm\Relator\RecordSet;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

use ArrayObject;
use InvalidArgumentException;


class Simple extends ArrayObject implements RecordSet 
{
    protected $record ;
    
    public function __construct( array $records, $flags = null )
    {
        foreach ( $records as $record ) { $this->validRecord($record); }
        parent::__construct( $records, $flags );
        
    }

    protected function validRecord( Mappable $record ) : Mappable
    {
        if ( ! isset( $this->record ) ) {
            $this->record = $record;
            
        } else {
            if ( ! $record instanceof $this->record ) {
                throw new InvalidArgumentException( 
                    'Passed record of class '. get_class($record)
                    .' must be an instance of class '. get_class($this->record) .'. '
                  );
            }
        }
        return $record;
    }
    
    
    public  function offsetSet( $key, $value ) 
    {
        parent::offsetSet( $key, $this->validRecord($value) );
    }
    
    public function asArrays() : array 
    {
        return array_map( function($record) { return $record->asArray(); }, (array) $this );
    }
    
    
    
}
