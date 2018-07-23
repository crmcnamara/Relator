<?php

namespace BitBalm\Relator\Recorder;

use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\RecordSet;


trait PDOTrait 
{
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $results = $this->loadRecordsByColumnValues( $record, $record->getPrimaryKeyName(), [ $record_id ] )
            ->asArrays();
        
        if ( count($results) >1 ) { 
            throw new Exception( 
                "Multiple {$record->getTableName()} records loaded for {$record->getPrimaryKeyName()}: {$record_id} " 
              ) ; 
        }
        
        if ( count($results) <1 ) { 
            throw new InvalidArgumentException( 
                "No {$record->getTableName()} records found for {$record->getPrimaryKeyName()}: {$record_id} " 
              ) ; 
        }
        
        // transfer values to the passed record
        $values = current($results);
        
        $record
            ->setValues($values)
            ->setUpdateId( $values[ $record->getPrimaryKeyName() ] ?? null );
            
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
        
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        $statement->execute($values);
        $results = $statement->fetchAll();
        
        $records = [];
        foreach ( $results as $idx => $values ) {
            $records[$idx] = $record->newRecord()
                ->setValues($values)
                ->setUpdateId( $values[ $record->getPrimaryKeyName() ] ?? null );
        }
        
        // fetch the record's preferred recordset
        $recordset = $record->asRecordSet();
        // and instantiate a new one of the same type, with these new result records
        $recordset = new $recordset($records);
        
        return $recordset;
    }
    
    public function saveRecord( Recordable $record ) : Recordable 
    {
        if ( ! is_null($record->getUpdateId()) ) {
            $this->updateRecord($record);
            
        } else {
            $this->insertRecord($record);
        }
        
        $this->loadRecord( $record, $record->getUpdateId() );
        
        return $record;
    }
    
    protected function insertRecord( Recordable $record ) : Recordable
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
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        $statement->execute(array_values( $values ));
        $inserted_id = $this->pdo->lastInsertId();
        
        $record->setUpdateId( $inserted_id );
        
        return $record;
    }
    
    protected function updateRecord( Recordable $record ) : Recordable
    {
        $table = $this->getValidator()->validTable($record->getTableName());
        $prikey = $this->getValidator()->validColumn( $table, $record->getPrimaryKeyName() );
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->getValidator()->validColumn( $table, $column ); }
        $update_id = $record->getUpdateId();
        
        $setstrings = [];
        foreach ( $values as $column => $value ) {
            $setstrings[$column] = " {$column} = ? ";
        }
        
        $querystring = 
            "UPDATE {$table} set "
                . implode( ' , ', $setstrings )
            ." WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        $query_values = array_values( $values );
        $query_values[] = $update_id;
        
        $statement->execute($query_values);
        $affected = $statement->rowCount();
        
        if ( array_key_exists( $prikey, $values ) ) { $update_id = $values[$prikey]; }
        $record->setUpdateId( $update_id );
        
        return $record;
    }
    
    public function deleteRecord( Recordable $record ) 
    {
        $table = $this->getValidator()->validTable($record->getTableName());
        $prikey = $this->getValidator()->validColumn( $table, $record->getPrimaryKeyName() );
        $update_id = $record->getUpdateId();
        
        $querystring = "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
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
