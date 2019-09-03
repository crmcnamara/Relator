<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use Exception;
use InvalidArgumentException; 
use PDO;
use PDOStatement;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Table as DoctrineTable;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;


trait DoctrineImplementation /* implements Vinyl\RecordStore\SQL\PDO */
{
    use PDOImplementation;
    
    
    protected $query_builder;
    protected $record;
    protected $records;
    protected $doctrine_table;
    
    
    public function __construct( 
        string $table_name, 
        QueryBuilder $query_builder,
        Vinyl\Record $record,
        Collection\Records $records
      )
    {
        $this->pdo              = $query_builder->getConnection()->getWrappedConnection();
        $this->query_builder    = clone $query_builder;        
        $this->record           = $record;
        $this->records          = $records;
        
        $this->table_name       = $table_name;
        $this->primary_key_name = $this->getPrimaryKey();
        $this->field_names      = $this->getFieldNames();
    }
    
    
    
    protected function getDoctrineTable() : DoctrineTable
    {
        if ( empty($this->doctrine_table) ) { 
            $this->doctrine_table = $this->query_builder->getConnection()->getSchemaManager()
                ->listTableDetails($this->table_name);
        }
        
        if ( empty( $this->doctrine_table->getColumns() ) ) { 
            throw new InvalidArgumentException("Invalid table for connection: {$this->getTable()} ");
        }
                
        return $this->doctrine_table;
    }
    
    public function getPrimaryKey() : string
    {
        if ( ! empty($this->primary_key_name) ) { return $this->primary_key_name; }

        $indexes = $this->getDoctrineTable()->getIndexes(); 
        $primary_columns = [] ;
        foreach ( $indexes as $index_name => $index ) {
            if ( $index->isPrimary() ) {
                foreach ( $index->getColumns() as $column_name ) { 
                    $primary_columns[$column_name] = $column_name; 
                }
            }
        }
         
        if ( count( $primary_columns ) >1 ) {
            throw new Exception(
                "Multiple primary keys are not supported for table {$this->getTable()} . "
              );
        }

        $primary_key = current( $primary_columns );
        
        if ( empty($primary_key) ) {
            throw new Exception("No primary key found for table {$this->getTable()}. ");
        }
        
        return $primary_key;
    }
    
    protected function getFieldNames()
    {
        return array_keys( $this->getDoctrineTable()->getColumns() );
    }
    
    
    /* implements Vinyl\RecordStore */
    
    public function getSelectQuery( string $field, array $values ) : QueryBuilder
    {
        $query = clone $this->query_builder;
        $query->select('*')->from( $this->getTable() )
            ->where( $query->expr()->in( 
                $this->validField($field), 
                implode( ', ', array_pad( [], count($values), '?' ) )
              ) );
        foreach ( array_values($values) as $idx => $value ) { $query->setParameter( $idx, $value ); }
            
        return $query;
    }
    
    public function getRecordsByFieldValues( string $field, array $values ) : Collection\Records 
    {
        // Mysql, for one, does not handle empty "IN ()" conditions well. 
        if ( empty($values) ) { return clone $this->records; }
        
        $query = $this->getSelectQuery( $field, $values );

        return $this->getRecordsByQueryString( $query->getSQL(), $query->getParameters() );
    }
 
 
    public function getInsertQuery( array $values ) : QueryBuilder
    {
        $query = clone $this->query_builder;
        $query->insert( $this->getTable() );
            
        $parameters = [];
        foreach ( $values as $field => $value ) { 
            $query->setValue( $this->validField($field), ':'. $field );
            $parameters[ ':'. $field ] = $value; 
        }
        $query->setParameters($parameters);
        
        return $query;
    }
    
    public function insertRecord( array $values ) : Record 
    {    
        $query = $this->getInsertQuery($values);

        $this->executeQuery($query);

        return $this->getRecord( $query->getConnection()->lastInsertId() );
    }


    public function getUpdateQuery( $record_id, array $updated_values ) : QueryBuilder
    {
        $query = clone $this->query_builder;
        $query->update( $this->getTable() );
        
        $param_idx = 0;        
        foreach ( $updated_values as $field => $value ) { 
            $query
                ->set( $this->validField($field), '?' )
                ->setParameter( $param_idx++,$value );
        }
        
        $query->where( $query->expr()->in( $this->getPrimaryKey(), '?' ) )
            ->setParameter( $param_idx, $record_id );
        
        return $query;
    }
    
    public function updateRecord( Record $record ) : Record 
    {
        $updated_values = $record->getUpdatedValues();
        $id_field = $this->getPrimaryKey();
        $record_id = $record->getRecordId();

        $query = $this->getUpdateQuery( $record_id, $updated_values );

        $affected = $this->executeQuery($query);        
        
        if ( 
            // If we're moving the record by changing its id,
            array_key_exists( $id_field, $updated_values ) and
            $updated_values[$id_field] != $record_id 
          )
        {
            // and the update didn't have any affect,
            if ( $affected <1 ) {
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

    
    public function getDeleteQuery( $record_id ) : QueryBuilder
    {
        $query = clone $this->query_builder;
        $query->delete( $this->getTable() )
            ->where( $query->expr()->in( $this->getPrimaryKey(), '?' ) )
            ->setParameter( 0, $record_id );
        return $query;
    }
    
    public function deleteRecord( Record $record )
    {
        $record_id = $record->getRecordId();
        $query = $this->getDeleteQuery($record_id);

        $affected = $this->executeQuery($query);
        
        if ( $affected <1 ) {
            throw new RecordNotFound(
                "An attempt to delete {$this->getTable()} record id {$record_id} did not affect any records. "
              );
        }
    }
    
    
    protected function executeQuery( QueryBuilder $query ) : int 
    {
        return $this->execute( $query->getSQL(), $query->getParameters() );
    }
    
}
    
