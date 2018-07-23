<?php

namespace BitBalm\Relator\Record;

use Exception;
use InvalidArgumentException;
use ArrayObject;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;

/* The Generic Record is a single class that can be used 
 *    to represent records from //any// table in the database schema. 
 * The table and its primary key must be provided as constructor arguments
 */
class Generic extends ArrayObject implements Record
{
    use RecordTrait;
    
    
    protected $generic_table_name;
    
    protected static $recorders;
    protected static $relators;
    
    
    public function __construct( string $table_name )
    {
        parent::__construct( [], ArrayObject::ARRAY_AS_PROPS );
        
        $this->setTableName( $table_name ) ;
    }

    public function setTableName( string $table_name ) : Generic
    {
        if ( $this->generic_table_name and $this->generic_table_name !== $table_name ) {
            throw InvalidArgumentException('A table name for this record is already set. ');
        }
        
        $this->generic_table_name = $table_name ;
        
        return $this ;
    }
    
    public function getTableName() : string
    {
        #TODO: throw Exception instead of TypeError when not set?
        return $this->generic_table_name ;
    }
    
    public function setRecorder( Recorder $recorder ) : Recordable
    {
        $table = $this->getTableName();
        
        if ( !empty(self::$recorders[$table]) and self::$recorders[$table] !== $recorder ) {
            throw new InvalidArgumentException("This record's Recorder is already set. ");
        }
        
        self::$recorders[$table] = $recorder;
        
        return $this;
    }
    
    public function getRecorder() : Recorder 
    {
        #TODO: throw Exception instead of TypeError when not set?
        return self::$recorders[$this->getTableName()];
    }
    
    public function setRelator( Relator $relator ) : Relatable
    {
        $table = $this->getTableName();
        
        if ( !empty(self::$relators[$table]) and self::$relators[$table] !== $relator ) {
            throw new InvalidArgumentException("This record's Relator is already set. ");
        }
        
        self::$relators[$table] = $relator;
        
        return $this;
    }
    
    public function getRelator() : Relator 
    {
        #TODO: throw Exception instead of TypeError when not set?
        return static::$relators[$this->getTableName()];
    }
    
    
    public function setRelationship( Relationship $relationship, string $relationship_name = null ) : GetsRelatedRecords
    {
        
        if ( empty($relationship_name) ) { $relationship_name = $relationship->getToTable()->getTableName(); }
        
        $from_table_name = $this->getTableName();

        $existing = isset( static::$relationships[$from_table_name][$relationship_name] )
            ? static::$relationships[$from_table_name][$relationship_name] : null ;
            
        if ( $relationship === $existing ) { return $this ; }
        
        if ( $existing instanceof Relationship ) {
            throw new InvalidArgumentException(
                "A relationship to {$relationship_name} is already set. "
              );
        }
        
        if ( ! ( $relationship->getFromTable() instanceof $this ) ) {
            throw new InvalidArgumentException(
                "The given Relationship's from table must be an instance of ". static::class .". "
              );
        }
        
        if ( $from_table_name !== $relationship->getFromTable()->getTableName() ) {
            throw new InvalidArgumentException(
                "The given Relationship's from table must have a table name of {$from_table_name}. "
              );
        }
        
        static::$relationships[$from_table_name][$relationship_name] = $relationship;
        
        return $this;
    }
    
    public function getRelationship( string $relationship_name ) : Relationship
    {
        #TODO: throw Exception instead of TypeError when not set?
        return static::$relationships[$this->getTableName()][$relationship_name]; 
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
