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
    
    public function __construct( string $table, array $values = [] )
    {
        parent::__construct( $values, ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTable( $table ) ;
    }
    
    protected function setTable( string $table ) : GenericRecord
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
    
    public function getTable() : string
    {
        return $this->relatorTable ;
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
}
