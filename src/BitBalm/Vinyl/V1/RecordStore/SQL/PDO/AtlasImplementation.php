<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use InvalidArgumentException;
use PDO;
use PDOStatement;

use Atlas\Pdo\Connection;
use Atlas\Info\Info as SchemaInfo;
use Atlas\Query\QueryFactory;
use Atlas\Query\Query;
use Atlas\Query\Select;
use Atlas\Query\Insert;
use Atlas\Query\Update;
use Atlas\Query\Delete;



use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\RecordStore\SQL\PDO\Atlas\Factory as AtlasFactory;


trait AtlasImplementation /* implements Vinyl\RecordStore\SQL\PDO */
{
    use PDOImplementation;
    
    
    protected $connection;
    protected $schema_info;
    protected $query_factory;
    protected $record;
    protected $records;
    protected $columns;
    
    
    public function __construct( 
        string $table_name, 
        AtlasFactory $atlas_factory,
        Vinyl\Record $record,
        Collection\Records $records
      )
    {
        $this->connection       = $atlas_factory->getConnection();
        $this->schema_info      = $atlas_factory->getInfo();
        $this->pdo              = $this->connection->getPdo();
        $this->query_factory    = $atlas_factory->getQueryFactory();
        $this->record           = $record;
        $this->records          = $records;
        
        $this->table_name       = $this->validTable($table_name);
        $this->primary_key_name = $this->getPrimaryKey();
        $this->field_names      = $this->getFieldNames();
    }
    
    protected function validTable( string $table ) : string
    {
        $tables = $this->schema_info->fetchTableNames();
        $valid_table = array_combine( $tables, $tables )[$table] ?? null;
        
        if ( empty($valid_table) ) {
            throw new InvalidArgumentException("Invalid table for connection: {$table} ");
        }
        
        return $valid_table;
    }
    
    protected function getColumns()
    {
        if ( empty($this->columns) ) { 
            $this->columns = $this->schema_info->fetchColumns( $this->getTable() );
        }
        
        return $this->columns; 
    }
    
    public function getPrimaryKey() : string
    {
        if ( ! empty($this->primary_key_name) ) { return $this->primary_key_name; }
        
        $primary_key = null;
        foreach ( $this->getColumns() as $column => $attributes ) {
            if ( ! empty($attributes['primary']) ) {
                if ( 
                    ( ! empty($primary_key) ) and 
                    $primary_key !== $column
                  )
                {
                    throw new Exception(
                        "Multiple primary keys {$primary_key}, {$column} are not supported for table {$this->getTable()} . "
                      );
                }
                $primary_key = $column;
            }
        }
        
        if ( empty($primary_key) ) {
            throw new Exception("No primary key found for table {$table->getTable()}. ");
        }
        
        return $primary_key;
    }
    
    protected function getFieldNames()
    {
        return array_keys( $this->getColumns() );
    }
    
    
    /* implements Vinyl\RecordStore */
    
    public function getSelectQuery( string $field, array $values ) : Select
    {
        $field = $this->validField($field);
        
        $query = $this->query_factory->newSelect( $this->connection );
        $query
            ->columns('*')
            ->from( $query->quoteidentifier($this->getTable()) )        
            ->where( $field .' IN ', $values )
            #TODO ->where()->orWhere()....->orWhere()... ?
            ;
        return $query;

    }
    
    public function getRecordsByFieldValues( string $field, array $values ) : Collection\Records 
    {
        // Mysql, for one, does not handle empty "IN ()" conditions well. 
        if ( empty($values) ) { return clone $this->records; }
        
        $field = $this->validField($field);
        
        $query = $this->getSelectQuery( $field, $values );
        return $this->getRecordsByQueryString( 
            $query->getStatement(), 
            $this->normalizeBindValues( $query->getBindValues() )
          );
    }
 
    
    public function getInsertQuery( array $values ) : Insert
    {
        $query = $this->query_factory->newInsert( $this->connection );
        $query->into( $this->getTable() );
        foreach ( $values as $field => $value ) {
            $query->column( $this->validField($field), $value );
        }

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
        $query->table( $this->getTable() );
        foreach ( $updated_values as $field => $value ) {
            $query->column( $this->validField($field), $value );
        }
        $query->where( $this->getPrimaryKey() .' = ', $record_id );
        
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
            $updated_values[$id_field] != $record_id 
          )
        {
            // and the update didn't have any affect,
            if ( $pdo_statement->rowCount() <1 ) { 
                throw new RecordNotFound(
                    "An attempt to change a {$this->getTable()} record's id "
                        ."from {$record_id} to {$updated_values[$id_field]} "
                        ."did not affect any records. "
                  ); 
            }
            
            $record_id = $updated_values[$id_field];
        } 
        
        $updated_record = $this->getRecord($record_id);
        
        // re-initialize the same record object that was passed to us. 
        $record->initializeRecord( $record_id, $updated_record->getAllValues() );
        
        return $record;
    }

    
    public function getDeleteQuery( $record_id ) : Delete
    {
        $query = $this->query_factory->newDelete( $this->connection );
        $query->from( $this->getTable() )
            ->where( $this->getPrimaryKey() .' = ', $record_id );
            
        return $query;
    }
    
    public function deleteRecord( Record $record )
    {
        $pdo_statement = $this->getDeleteQuery( $record->getRecordId() )->perform();
        
        if ( $pdo_statement->rowCount() <1 ) {
            throw new RecordNotFound;
        }
    }
    
    
    protected function normalizeBindValues( array $bind_values )
    {
        foreach ( $bind_values as $element ) {
            if ( ! ( 
                is_array($element) and
                array_key_exists( 0, $element )
              ) )
            {
                throw new InvalidArgumentException(
                    "Invalid Atlas bind values: \n". var_export($bind_values,true)
                  );
            }
        }
        
        $normalized = array_combine( array_keys($bind_values), array_column( $bind_values, 0 ) );

        return $normalized;
    }
    
    protected function executeQuery( Query $query ) : int 
    {
        return $this->execute(
            $query->getStatement(), 
            $this->normalizeBindValues( $query->getBindValues() )
          );
    }
    
}
