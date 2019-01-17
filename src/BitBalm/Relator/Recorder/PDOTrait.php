<?php

namespace BitBalm\Relator\Recorder;

use PDO;
use Exception;
use InvalidArgumentException;
use RuntimeException;


use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Mapper\PDO\SchemaValidator;
use BitBalm\Relator\Exception\RecordNotFound;
use BitBalm\Relator\Exception\TooManyRecords;

trait PDOTrait 
{
    abstract function getPdo() : PDO ;
    abstract function getValidator() : SchemaValidator ;
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $results = $this->loadRecordsByColumnValues( $record, $record->getPrimaryKeyName(), [ $record_id ] )
            ->asArrays();
        
        if ( count($results) >1 ) { 
            throw new TooManyRecords( 
                "Multiple {$record->getTableName()} records loaded for {$record->getPrimaryKeyName()}: {$record_id} " 
              ) ; 
        }
        
        if ( count($results) <1 ) { 
            throw new RecordNotFound( 
                "No {$record->getTableName()} records found for {$record->getPrimaryKeyName()}: {$record_id} " 
              ) ; 
        }
        
        // transfer values to the passed record
        $values = current($results);
        
        $record
            ->setValues($values)
            ->setUpdateId( $values[ $record->getPrimaryKeyName() ] );
            
        return $record;
    }
    
    public function loadRecords( Recordable $record, array $record_ids ) : RecordSet 
    {
        return $this->loadRecordsByColumnValues( $record, $record->getPrimaryKeyName(), $record_ids );
    }
    
    public function loadRecordsByColumnValues( Recordable $record, string $column, array $values ) : RecordSet 
    {
        $table  = $this->getValidator()->validTable($record->getTableName());
        $column = $this->getValidator()->validColumn( $table, $column );
        
        $querystring = 
            "SELECT * from {$table} where {$column} in ( "
              . implode( ', ', array_pad( [],  count($values), '?' ) ) 
            ." ) ";
        
        $statement = $this->getPdo()->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        $statement->execute($values);
        $results = $statement->fetchAll();
        
        $records = [];
        foreach ( $results as $idx => $values ) {
            $records[$idx] = $record->newRecord()
                ->setValues($values)
                ->setUpdateId( $values[ $record->getPrimaryKeyName() ] );
        }
        
        // fetch the record's preferred recordset
        $recordset = $record->asRecordSet();
        // and instantiate a new one of the same type, with these new result records
        $recordset = new $recordset($records);
        
        return $recordset;
    }
    
    public function saveRecord( Recordable $record, $update_id = null ) : Recordable 
    {
        // use the explicit argument first, falling back on the update id set in the record
        if ( $update_id === null ) { $update_id = $record->getUpdateId(); }
        
        if ( $update_id !== null ) {
            $this->updateRecord( $update_id, $record );
          
        } else {
            $this->insertRecord($record);
        }
        
        $this->loadRecord( $record, $record->getUpdateId() );
        
        return $record;
    }
    
    public function insertRecord( Recordable $record ) : Recordable
    {
        
        $table = $this->getValidator()->validTable($record->getTableName());
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->getValidator()->validColumn( $table, $column ); }
                
        $querystring = 
            "INSERT into {$table} ( "
                . implode( ' , ', array_keys( $values ) )
            ." ) VALUES ( "
                . implode( ' , ', array_pad( [], count($values), '?' ) )
            ." ) ";
            
        $statement = $this->getPdo()->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        $statement->execute(array_values( $values ));
        $inserted_id = $this->getPdo()->lastInsertId();
        
        $record->setUpdateId( $inserted_id );
        
        return $record;
    }
    
    public function updateRecord( $update_id, Recordable $record ) : Recordable
    {
        $table = $this->getValidator()->validTable($record->getTableName());
        $prikey = $this->getValidator()->validColumn( $table, $record->getPrimaryKeyName() );
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->getValidator()->validColumn( $table, $column ); }
        
        $setstrings = [];
        foreach ( $values as $column => $value ) {
            $setstrings[$column] = " {$column} = ? ";
        }
        
        $querystring = 
            "UPDATE {$table} set "
                . implode( ' , ', $setstrings )
            ." WHERE {$prikey} = ? ";
            
        $statement = $this->getPdo()->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $query_values = array_values( $values );
        $query_values[] = $update_id;
        
        $statement->execute($query_values);
        $affected = $statement->rowCount();
        
        if ( array_key_exists( $prikey, $values ) ) { $update_id = $values[$prikey]; }
        $record->setUpdateId($update_id);
        
        return $record;
    }
    
    public function deleteRecord( Recordable $record ) 
    {
        $table = $this->getValidator()->validTable($record->getTableName());
        $prikey = $this->getValidator()->validColumn( $table, $record->getPrimaryKeyName() );
        $update_id = $record->getUpdateId();
        
        $querystring = "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->getPdo()->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        $statement->execute([ $update_id ]);
        $affected = $statement->rowCount();
        
        return $affected;
    }
    
    public function getPrimaryKeyName( string $table_name ) : string 
    {
        return $this->getValidator()->getPrimaryKeyName($table_name);
    }
}
