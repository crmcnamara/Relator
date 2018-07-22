<?php

namespace BitBalm\Relator\Record;

use Exception;
use InvalidArgumentException;
use ArrayObject;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

/* The Generic Record is a single class that can be used 
 *    to represent records from //any// table in the database schema. 
 * The table and its primary key must be provided as constructor arguments
 */
class Generic extends ArrayObject implements Mappable, Recordable, Relatable, GetsRelatedRecords
{
    use RecordTrait;
    
    
    protected $generic_table_name;
    protected $generic_primary_key_name;
    
    
    public function __construct( string $table_name, string $primary_key_name )
    {
        parent::__construct( [], ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTableName( $table_name ) ;
        $this->setPrimaryKeyName( $primary_key_name );
    }

    public function setTableName( string $table_name ) : Generic
    {
        if ( $table_name === $this->generic_table_name ) { return $this ; }
        
        if ( is_string( $this->generic_table_name ) ) {
            throw InvalidArgumentException('A table name for this Record is already set. ');
        }
        
        $this->generic_table_name = $table_name ;
        
        return $this ;
        
    }
    
    public function getTableName() : string
    {
        return $this->generic_table_name ;
    }
    
    public function setPrimaryKeyName( string $key_name ) : Generic
    {
        if ( $key_name === $this->generic_primary_key_name ) { return $this ; }
        
        if ( is_string( $this->generic_primary_key_name ) ) {
            throw InvalidArgumentException('A primary key name for this Record is already set. ');
        }
        
        $this->generic_primary_key_name = $key_name ;
        
        return $this ;
    }
    
    public function getPrimaryKeyName() : string
    {
        return $this->generic_primary_key_name ;
    }

    public function newRecord() : Mappable 
    {
        return new static( $this->getTableName(), $this->getPrimaryKeyName() );
    }
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
    public function setValues( array $values ) : Mappable 
    {
        $this->exchangeArray(array_replace( (array) $this->getArrayCopy(), $values )) ;
        return $this;
    }
    
}
