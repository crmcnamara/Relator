<?php

namespace BitBalm\Relator\Record;

use Exception;
use InvalidArgumentException;
use ArrayObject;

use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;


class Generic extends ArrayObject implements Record, Recordable, Relatable, GetsRelatedRecords
{
    use RelatableTrait, RecordableTrait;
    
    protected $tableName ;
    protected $primary_key_name;
    
    
    public function __construct( string $tableName, string $primary_key_name )
    {
        parent::__construct( [], ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTableName( $tableName ) ;
        $this->setPrimaryKeyName( $primary_key_name );
    }
    
    protected function setTableName( string $tableName ) : Record\Generic
    {
        if ( $tableName === $this->tableName ) { return $this ; }
        
        if ( is_string( $this->tableName ) ) {
            throw InvalidArgumentException('A table name for this Record is already set. ');
        }
        
        $this->tableName = $tableName ;
        
        return $this ;
        
    }
    
    protected function setPrimaryKeyName( string $key_name )
    {
        if ( $key_name === $this->primary_key_name ) { return $this ; }
        
        if ( is_string( $this->primary_key_name ) ) {
            throw InvalidArgumentException('A primary key name for this Record is already set. ');
        }
        
        $this->primary_key_name = $key_name ;
        
        return $this ;
    }
    
    public function getTableName() : string
    {
        return $this->tableName ;
    }
    
    public function getPrimaryKeyName() : string
    {
        return $this->primary_key_name ;
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
    public function newRecord() : Record 
    {
        return new static( $this->getTableName(), $this->getPrimaryKeyName() );
    }
    
    public function setValues( array $values ) : Record 
    {
        $this->exchangeArray(array_replace( (array) $this->getArrayCopy(), $values )) ;
        return $this;
    }
    
}
