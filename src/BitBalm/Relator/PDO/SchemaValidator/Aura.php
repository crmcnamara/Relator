<?php

namespace BitBalm\Relator\PDO\SchemaValidator;

use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\SchemaValidator;


class Aura implements SchemaValidator
{
    protected $schema;
    protected static $columns = [] ;
 
    
    public function __construct( SchemaInterface $schema ) 
    {
        $this->schema = $schema;
        $this->refreshSchema();
    }
    
    public function refreshSchema()
    {
        $tables = $this->schema->fetchTableList();
        static::$columns = [];
        foreach ( (array) $tables as $table ) {
            static::$columns[$table] = array_column( $this->schema->fetchTableCols($table), 'name' );
        }
    }
    
    public function isValidTable( string $table ) : bool
    {
        return in_array( $table, array_keys(static::$columns), true ) ;
    }
    
    public function validTable( string $table ) : string
    {
        if ( ! $this->isValidTable( $table ) ) {
            throw new InvalidArgumentException("Table '{$table}' does not exist in the database. ");
        }
        return $table;
    }
    
    public function isValidColumn( string $table, string $column ) : bool
    {
        return in_array( $column, static::$columns[$table], true ) ;
    }
    
    public function validColumn( string $table, string $column ) : string
    {
        if ( ! $this->isValidColumn( $table, $column ) ) {
            throw new InvalidArgumentException("Column '{$column}' does not exist in the database table '{$table}' . ");
        }
        return $column;
    }
    
}
