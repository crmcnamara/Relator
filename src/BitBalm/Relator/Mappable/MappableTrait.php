<?php

namespace BitBalm\Relator\Mappable;


use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\AlreadySetException;


class TableNameAlreadySet extends InvalidArgumentException implements AlreadySetException {}


Trait MappableTrait 
{
    protected static $table_name;
    
    protected $record_values = [];
    
    public function asArray() /*: array*/
    {
        return $this->record_values;
    }
    
    public function setValues( array $values ) /*: Mappable*/ 
    {
        $this->record_values = array_replace( (array) $this->record_values, $values ) ;
        return $this;
    }
    
    public function newRecord() /*: Mappable*/
    {
        return new static;
    }
    
    public function asRecordSet( RecordSet $recordset = null ) /*: RecordSet*/
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\Mappable([ $this ]);
    }
    
    public function setTableName( /*string*/ $table_name ) /*: Mappable*/
    {
        $existing_name = static::$table_name;
        
        if ( $existing_name === $table_name ) { return $this; }
        
        if ( !is_null($existing_name) )  {
            throw new TableNameAlreadySet(
                "The table name for this object is already set to: {$existing_name}. "
              );
        }
        
        static::$table_name = $table_name;
        
        return $this;
        
    }
    
    public function getTableName() /*: string*/
    {
        #TODO: throw Exception instead of TypeError when not set?
        return static::$table_name;
    }
    
}
