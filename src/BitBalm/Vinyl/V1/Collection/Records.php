<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;

use Traversable;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;

class Records extends Vinyl\Collection implements Vinyl\RecordProducer
{
    public function __construct( array $items = [], int $flags = 0  )
    {
        parent::__construct( $items, $flags );
        $this->rewind();
    }
    
    public function validItem( $item ) : Vinyl\Record
    {
        return $item;
    }
    
    public function current() : Vinyl\Record
    {
        return $this->validItem( parent::current() );
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
    public function asArrays() : array
    {
        return array_map( 
            function( Vinyl\Record $record ) { return $record->getAllValues(); }, 
            $this->asArray()
          );
    }
    
    public function getMasterRecord() : Vinyl\Record
    {
        if ( $this->count() <1 ) { 
            throw new RecordNotFound( "No master record was found for this Colleciton. " ); 
        }
        
        return current( $this->getArrayCopy() );
    }
}
