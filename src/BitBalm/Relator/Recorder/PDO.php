<?php

namespace BitBalm\Relator\Recorder;

use Exception;

use BitBalm\Relator\Recorder;


class PDO implements Recorder
{
    protected $pdo ;
    
    public function __construct( \PDO $pdo ) 
    {
        $this->pdo = $pdo;
    }
        
    public function getPDO() : \PDO
    {
        return $this->pdo;
    }
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $table = $record->getTableName();
        $prikey = $record->getPrimaryKeyName();
        
        #TODO: validation/escaping of table and prikey
        
        $querystring = "SELECT * from {$table} where {$prikey} = ? ";
        
        $statement = $this->getPDO()->prepare( $querystring );
        $statement->bindValue( 1, $record_id );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute();
        $results = $statement->fetchAll();
        
        if ( count($results) >1 ) { throw Exception( "multiple {$table} records loaded for id: {$record_id} " ) ; }
        
        if ( count($results) <1 ) { return null; }
        
        $loaded_record = $record->createFromArray(current($results));
        
        return $loaded_record;
    }
    
    public function saveRecord( Recordable $record ) : Recordable 
    {
        if ( !empty($record->getLoadedId()) ) {
            return $this->updateRecord($record);
            
        } else {
            return $this->insertRecord($record);
        }
    }
    
    protected function insertRecord( Recordable $record ) : Recordable
    {
        $values = $record->asArray();
        $table = $record->getTableName();
        
        #TODO: validation/escaping of $table 
        
        $querystring = 
            "INSERT into {$table} ( "
                . implode( ' , ', array_keys( $values ) )
            ." ) VALUES ( "
                . implode( ' , ', array_pad( [], count($values), '?' ) )
            ." ) ";
            
        $statement = $this->getPDO()->prepare( $querystring );
        foreach ( array_values($values) as $index => $value ) {
            $statement->bindValue( $index +1, $value );
        }
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute();
        $inserted_id = $this->getPDO()->lastInsertId();
        
        $inserted_record = $this->loadRecord( $record, $insert_id );
        
        #TODO: consider attempting to update record values and recorder_loaded_id on the original record object
        
        return $inserted_record;
    }
    
    protected function updateRecord( Recordable $record ) : Recordable
    {
        $values = $record->asArray();
        $table  = $record->getTableName();
        $prikey = $record->getPrimaryKeyName();
        $update_id = $record->getLoadedId();
        
        $setstrings = [];
        foreach ( $values as $column => $value ) {
            $setstrings[$column] = " {$column} = ? ";
        }
        
        #TODO: validation/escaping of $table, $column, $prikey
        
        $querystring = 
            "UPDATE {$table} set "
                . implode( ' , ', $setstrings )
            ." WHERE {$prikey} = ? ";
            
        $statement = $this->getPDO()->prepare( $querystring );
        foreach ( array_values($values) as $index => $value ) {
            $statement->bindValue( $index +1, $value );
        }
        $statement->bindValue( count($values) +1, $update_id );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute();
        $affected = $statement->rowCount();
        
        $updated_record = $this->loadRecord( $record, $record->getLoadedId() );
        
        #TODO: consider attempting to update record values and recorder_loaded_id on the original record object
        
        return $updated_record;
    }
    
    public function deleteRecord( Recordable $record ) 
    {
        $table  = $record->getTableName();
        $prikey = $record->getPrimaryKeyName();
        $update_id = $record->getLoadedId();
        
        #TODO: validation/escaping of $table and $prikey
        
        $querystring = 
            "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->getPDO()->prepare( $querystring );
        $statement->bindValue( 1, $record->getLoadedId() );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute();
        $affected = $statement->rowCount();
        
        return $affected;
    }
}
