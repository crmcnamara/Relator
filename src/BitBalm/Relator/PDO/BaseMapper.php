<?php

namespace BitBalm\Relator\PDO;

use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;


/** This serves as the common basis for the Relator\PDO and Recorder\PDO implementations
 */
abstract class BaseMapper
{
    
    protected $pdo ;
    protected $schema;
    protected $columns = [] ;
    
    public function __construct( \PDO $pdo, SchemaInterface $schema ) 
    {
        $this->pdo = $pdo;
        $this->schema = $schema;
        $this->refreshSchema();
    }
    
    protected function refreshSchema()
    {
        $tables = $this->schema->fetchTableList();
        foreach ( (array) $tables as $table ) {
            $this->columns[$table] = array_column( $this->schema->fetchTableCols($table), 'name' );
        }
    }
    
    public function validTable( string $table ) 
    {
        if ( ! in_array( $table, array_keys($this->columns), true ) ) {
            throw new InvalidArgumentException("Table '{$table}' does not exist in the database. ");
        }
        return $table;
    }
    
    public function validColumn( string $table, string $column ) 
    {
        
        if ( empty($this->columns[$table]) ) {
            $this->columns[$table] = array_column( $this->schema->fetchTableCols($table), 'name' );
        }
        
        if ( ! in_array( $column, $this->columns[$table], true ) ) {
            throw new InvalidArgumentException("Column '{$column}' does not exist in the database table '{$table}' . ");
        }
        return $column;
    }
    
}
