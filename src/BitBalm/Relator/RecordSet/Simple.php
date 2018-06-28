<?php 

namespace BitBalm\Relator\RecordSet;

use BitBalm\Relator\Record;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

use ArrayObject;
use InvalidArgumentException;


class Simple extends ArrayObject implements RecordSet 
{
    
    protected $record ;
    
    use GetsRelatedTrait;
    
    public function __construct( array $records, $flags = null )
    {
        $this->validateRecords( ...$records );
        
        parent::__construct( $records, $flags );
        
        if ( ! empty( $records ) ) {
            $this->record = current($this) ;
        }
        
    }

    protected function validateRecords( Record ...$records ) : RecordSet
    {
        #TODO: validate that $records are all of the same class?
        return $this;
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
    
    #TODO: call validateRecords() after every alteration! adding items, changing items, etc. 
    
}
