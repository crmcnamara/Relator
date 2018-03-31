<?php 

namespace BitBalm\Relator\RecordSet;

use BitBalm\Relator\Record;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedTrait;

use ArrayObject;
use InvalidArgumentException;



class Simple extends ArrayObject implements RecordSet 
{
    
    protected $record ;
    
    use GetsRelatedTrait;
    
    public function __construct( array $input = [], $flags = null )
    {
        parent::__construct( $input, $flags );
        
        if ( ! empty( $input ) ) {
            $this->record = current($this) ;
        }
        
        $this->validate();
        
    }
    
    public function validate() : Simple
    {
        
        $values = $this->getArrayCopy() ;
        
        if ( ! empty( $values ) ) {
          
            $class = null ;
            if ( gettype( $this->record ) === 'object' ) { 
                $class = get_class( $this->record ) ; 
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
    
    public function getTableName() : string
    {
        if ( $this->record instanceof Record ) { return $this->record->getTableName() ; }
        throw new Exception("This RecordSet's Record type is not yet set. ");
    }
    
    public function getRelator() : Relator
    {
        if ( $this->record instanceof Record ) { return $this->record->getRelator(); }
        throw new Exception("This RecordSet's Record type is not yet set. ");
    }
    
    public function getRelationship( string $relationshipName ) : Relationship
    {
        if ( $this->record instanceof Record ) { return $this->record->getRelationship( $relationshipName ); }
        throw new Exception("This RecordSet's Record type is not yet set. ");
    }
    
    public function asRecordSet() : RecordSet
    {
        return $this ;
    }
    
}
