<?php

namespace BitBalm\Relator\Recorder;

#use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recordable;


class PDO extends BaseMapper implements Recorder
{
    
    public function loadRecord( Recordable $record, $record_id ) : Recordable 
    {
        $table  = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        
        $querystring = "SELECT * from {$table} where {$prikey} = ? ";
        
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $record_id ]);
        $results = $statement->fetchAll();
        
        if ( count($results) >1 ) { throw Exception( "Multiple {$table} records loaded for id: {$record_id} " ) ; }
        
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
        
        $table = $this->validTable($record->getTableName());
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->validColumn( $table, $column ); }
        
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
        $table = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        $values = $record->asArray();
        foreach ( $values as $column => $value ) { $this->validColumn( $table, $column ); }
        $update_id = $record->getLoadedId();
        
        $setstrings = [];
        foreach ( $values as $column => $value ) {
            $setstrings[$column] = " {$column} = ? ";
        }
        
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
        $table = $this->validTable($record->getTableName());
        $prikey = $this->validColumn( $table, $record->getPrimaryKeyName() );
        $update_id = $record->getLoadedId();
        
        $querystring = "DELETE from {$table} WHERE {$prikey} = ? ";
            
        $statement = $this->pdo->prepare( $querystring );
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        $statement->execute([ $update_id ]);
        $affected = $statement->rowCount();
        
        return $affected;
    }
}
