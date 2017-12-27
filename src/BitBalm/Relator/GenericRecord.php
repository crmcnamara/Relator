<?php

namespace BitBalm\Relator;

use Exception;
use InvalidArgumentException;
use PDO;
use ArrayObject;

class GenericRecord extends ArrayObject implements Record
{
    use RecordTrait;
    
    protected $tableName ;
    
    public function __construct( string $tableName, GenericRecord $record = null )
    {
        parent::__construct( [], ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTable( $tableName ) ;
    }
    
    protected function setTable( string $tableName ) : GenericRecord
    {
        if ( $tableName === $this->tableName ) { return $this ; }
        
        if ( is_string( $this->tableName ) ) {
            throw InvalidArgumentException('A table name for this object is already set. ');
        }
        
        $this->tableName = $tableName ;
        
        return $this ;
        
    }
    
    public function getTableName() : string
    {
        return $this->tableName ;
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
    public function setArray( array $input ) : GenericRecord 
    {
        $this->exchangeArray($input);
        return $this;
    }
    
}
