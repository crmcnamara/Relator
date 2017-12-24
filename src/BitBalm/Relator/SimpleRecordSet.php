<?php 

namespace BitBalm\Relator;

use BitBalm\Relator\RecordSet;
use ArrayObject;
use InvalidArgumentException;

class SimpleRecordSet extends ArrayObject implements RecordSet 
{
    
    public $relatorTable ;
    
    use GetsRelatedTrait;
    
    public function __construct( array $input = [], $flags = null )
    {
        parent::__construct( $input, $flags );
        
        $this->validate();
        
        if ( ! empty( $input ) ) {
            $this->relatorTable = current($this)->getTable() ;
        }
    }
    
    public function validate() : SimpleRecordSet
    {
        
        $values = $this->getArrayCopy() ;
        

        
        if ( ! empty( $values ) ) {
          
            $class = null ;            
            if ( gettype( current( $values ) ) === 'object' ) { 
                $class = get_class( current( $values ) ) ; 
            }
            
            foreach ( $values as $item ) {
                
                if ( ! ( 
                      gettype( $item ) === 'object' 
                        and
                      $item instanceof Record
                        and
                      get_class( $item ) === $class
                  ) ) {
                    throw new InvalidArgumentException(
                      'All values must be of the same class and implement '. Record::class );
                }
                
            }
        }
        
        return $this ;
        
    }
    
    public function getTable() : string
    {
        if ( $firstitem = current($this) ) { 
            return $firstitem->getTable() ; 
        }
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
    
}
