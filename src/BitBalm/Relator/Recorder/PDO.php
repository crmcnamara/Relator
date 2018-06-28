<?php

namespace BitBalm\Relator\Recorder;

use Exception;

use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recordable;


class PDO implements Recorder
{
    protected $pdo ;
    
    public function __construct( \PDO $pdo ) 
    {
        $this->pdo = $pdo;
    }
    {
    }
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $table = $record->getTableName();
        $prikey = $record->getPrimaryKeyName();
        
        #TODO: validation/escaping of table and prikey
        
        $querystring = "SELECT * from {$table} where {$prikey} = ? ";
        
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $record_id ]);
        $results = $statement->fetchAll();
        
        if ( count($results) >1 ) { throw Exception( "Multiple {$table} records loaded for id: {$record_id} " ) ; }
        
        if ( count($results) <1 ) { return null; }
        
        $loaded_record = $record->loadFromArray(current($results));
        
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
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute(array_values( $values ));
        $inserted_id = $this->pdo->lastInsertId();
        
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
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $values = array_values( $values );
        $values[] = $update_id;
        
        $statement->execute($values);
        $affected = $statement->rowCount();
        
        $updated_record = $this->loadRecord( $record, $update_id  );
        
        #TODO: consider attempting to update record values and recorder_loaded_id on the original record object
        
        return $updated_record;
    }
    
    public function deleteRecord( Recordable $record ) 
    {
        $table  = $record->getTableName();
        $prikey = $record->getPrimaryKeyName();
        $update_id = $record->getLoadedId();
        
        #TODO: validation/escaping of $table and $prikey
        $querystring = "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $update_id ]);
        $affected = $statement->rowCount();
        
        return $affected;
    }
}
