<?php

namespace BitBalm\Relator;

use Exception;
use InvalidArgumentException;
use PDO;
use ArrayObject;

class GenericRecord extends ArrayObject implements Record
{
    use RecordTrait;
    
    protected $relatorTable ;
    
    public function __construct( $table, array $values = [] )
    {
        parent::__construct( $values, ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTable( $table ) ;
    }
    
    protected function setTable( $table )
    {
      
        if ( !empty( $this->relatorTable ) ) {
            throw Exception('Table is already set');
        }
        
        if ( empty( $table ) ) { 
            throw InvalidArgumentException('$table argument is empty. ') ; 
        }
        
        $this->relatorTable = $table ;
        
        return $this ;
        
    }
    
    public function getTable() {
        return $this->relatorTable ;
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
}
