<?php 

namespace BitBalm\Relator;

use BitBalm\Relator\RecordSet;
use ArrayObject;
use InvalidArgumentException;

class SimpleRecordSet extends ArrayObject implements RecordSet 
{
    use GetsRelatedTrait;
    
    public function __construct( array $input, $flags, $iterator_class )
    {
        // All items in $input array must be Records of the same class
        if ( ! empty( $input ) ) {
          
            $class = null ;            
            if ( gettype( current( $input ) ) === 'object' ) { 
                $class = get_class( current( $input ) ) ; 
            }
            
            foreach ( $input as $item ) {
                            
                if ( ! ( 
                      gettype( current( $item ) ) === 'object' 
                        and
                      $item instanceof Record
                        and
                      get_class( $item ) === $class                        
                  ) ) {
                    throw new InvalidArgumentException(
                      'All $input arguments must be of the same class and implement '. Record::class );
                }
                
            }
        }
            
        parent::__construct( $input, $flags, $iterator_class );
        
    }
    
    public function getTable() : string
    {
        if ( $firstitem = current( $this ) ) { 
            return $firstitem->getTable() ; 
        }
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }

}
