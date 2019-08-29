<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use PDO;
use PDOStatement;

use Atlas\Pdo\Connection;
use Atlas\Query\QueryFactory;
use Atlas\Query\Select;
use Atlas\Query\Insert;
use Atlas\Query\Update;
use Atlas\Query\Delete;



use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;


trait Methods /* implements Vinyl\RecordStore\SQL\PDO */
{
    protected $connection;
    protected $query_factory;
    protected $record;
    protected $records;
    
    
    public function __construct( 
        string $table_name, 
        string $primary_key_name, 
        PDO $pdo, 
        QueryFactory $query_factory,
        Vinyl\Record $record,
        Collection\Records $records
      )
    {
        parent::__construct( $table_name, $primary_key_name );
        
        $this->connection     = Connection::new($pdo);
        $this->query_factory  = $query_factory;
        $this->record         = $record;
        $this->records        = $records;
    }
    
    
    /* implements Vinyl\RecordStore */
    
    public function getRecord( $record_id ) : Record 
    {
        return $this->getRecordByFieldValue( $this->getPrimaryKey(), $record_id );
    }

    
    public function getRecords( array $record_ids ) : Collection\Records 
    {
        return $this->getRecordsByFieldValues( $this->getPrimaryKey(), $record_ids );
    }
    
    public function getRecordByFieldValue( string $field, $value ) : Record 
    {
        $records = $this->getRecordsByFieldValues( $field, [ $value ] );
        $this->hasOnlyOneRecord($records);
        return current($records);
    }
 
    public function getSelectQuery( string $field, array $values )
    {
        #TODO: validate $field
        $query = $this->query_factory->newSelect( $this->connection );
        $query
            ->columns('*')
            ->from( $query->quoteidentifier($this->getTable()) )        
            # TODO: handle empty $values
            ->where( $field .' IN ', $values )
            #TODO ->where()->orWhere()....->orWhere()... ?
            ;
        return $query;

    }
    
    public function getRecordsByFieldValues( string $field, array $values ) : Collection\Records 
    {
        $query = $this->getSelectQuery( $field, $values );            
        return $this->getRecordsByQueryString( $query->getStatement(), $query->getBindValues() );
    }
 
    
    
    public function getInsertQuery( array $values ) : Insert
    {
        $query = $this->query_factory->newInsert( $this->connection );
        $query
            ->into( $query->quoteidentifier( $this->getTable() ) )
            #TODO: validate columns
            ->columns($values);
        
        return $query;
    }
    
    public function insertRecord( array $values ) : Record 
    {    
        $query = $this->getInsertQuery($values);
        $query->perform();
        
        return $this->getRecord( $query->getLastInsertId() );
    }


    public function getUpdateQuery( $record_id, array $updated_values ) : Update
    {
        $query = $this->query_factory->newupdate( $this->connection );
        $query
            ->table( $query->quoteidentifier( $this->getTable() ) )
            #TODO: validate columns
            ->columns( $updated_values )
            ->where( 
                $query->quoteIdentifier($this->getPrimaryKey()) .' = ', 
                $record_id 
              );
        
        return $query;
    }
    
    public function updateRecord( Record $record ) : Record 
    {
        $updated_values = $record->getUpdatedValues();
        $id_field = $this->getPrimaryKey();
        $record_id = $record->getRecordId();
        
        $pdo_statement = $this->getUpdateQuery( $record_id, $updated_values )->perform();
        
        if ( 
            // If we're moving the record by changing its id,
            array_key_exists( $id_field, $updated_values ) and
            $updated_values[$id_field] != $record->getRecordId() and 
            // and the update didn't have any affect,
            $pdo_statement->rowCount() <1
          ) 
        {
            throw new RecordNotFound;
        } 
        
        $updated_record = $this->getRecord($record_id);
        
        $record->initializeRecord( $record_id, $updated_record->getAllValues() );
        
        return $record;
    }

    
    public function getDeleteQuery( $record_id ) : Delete
    {
        $query = $this->query_factory->newDelete( $this->connection );
        $query
            ->from( $query->quoteIdentifier($this->getTable()) )
            ->where( $query->quoteIdentifier($this->getPrimaryKey()) .' = ', $record_id )
            ;
        return $query;
    }
    
    public function deleteRecord( Record $record )
    {
        $pdo_statement = $this->getDeleteQuery( $record->getRecordId() )->perform();
        
        if ( $pdo_statement->rowCount() <1 ) {
            throw new RecordNotFound;
        }
    }
    
    
    /* implements Vinyl\RecordStore\SQL */
    public function getRecordsByQueryString( string $query, array $parameters ) : Collection\Records 
    {
#throw new \Exception(var_export([(__METHOD__?:__FILE__)=>__LINE__,func_get_args(),],true));
        $statement = $this->connection->perform( $query, $parameters );
        return $this->getRecordsByStatement($statement); 
    }
    
    
    /* implements Vinyl\RecordStore\SQL\PDO */
  
    public function getPDO() : PDO 
    {
        return $this->connection->getPdo();
    }

    public function getRecordByStatement( PDOStatement $statement, array $parameters ) : Record 
    {
        $records = $this->getRecordsByStatement( $statement, $parameters );
        $this->hasOnlyOneRecord($records);
        return current($records);
    }
    
    public function getRecordsByStatement( PDOStatement $statement, array $parameters = [] ) : Collection\Records 
    {
        // Some statements may already be executed - execute it ourselves only if provided parameters, or if it hasn't been executed.
        if ( !empty($parameters) or $statement->columnCount() == 0 ) { 
            $statement->execute($parameters); 
        }
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC); #TODO: generator?
        
        return $this->getRecordsFromRows($rows);
    }
    
    protected function getRecordsFromRows( array $rows ) : Collection\Records
    {
        $records = clone $this->records;
        foreach( $rows as $row ) {
            $record = clone $this->record;
            $record->initializeRecord( $row[$this->getPrimaryKey()], $row );
            $records[] = $record;
        }
        
        return $records;
    }
    

}
