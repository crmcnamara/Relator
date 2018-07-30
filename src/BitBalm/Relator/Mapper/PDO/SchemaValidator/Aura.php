<?php

namespace BitBalm\Relator\Mapper\PDO\SchemaValidator;

use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\Mapper\PDO\SchemaValidator;


class Aura implements SchemaValidator
{
    protected $schema;
    protected $column_names = [] ;
    protected $columns = [] ;
 
    
    public function __construct( SchemaInterface $schema ) 
    {
        $this->schema = $schema;
        $this->refreshSchema();
    }
    
    public function refreshSchema()
    {
        $tables = $this->schema->fetchTableList();
        $this->columns = [];
        foreach ( (array) $tables as $table ) {
            $this->columns[$table] = $this->schema->fetchTableCols($table);
            foreach ( $this->columns[$table] as $column ) { 
                $this->column_names[$table][$column->name] = $column->name;
            }
        }
    }
    
    public function isValidTable( /*string*/ $table ) /*: bool*/
    {
        return in_array( $table, array_keys($this->columns), true ) ;
    }
    
    public function validTable( /*string*/ $table ) /*: string*/
    {
        if ( ! $this->isValidTable( $table ) ) {
            throw new InvalidTable("Table '{$table}' does not exist in the database. ");
        }
        return $table;
    }
    
    public function isValidColumn( /*string*/ $table, /*string*/ $column ) /*: bool*/
    {
        return in_array( $column, $this->column_names[$table], true ) ;
    }
    
    public function validColumn( /*string*/ $table, /*string*/ $column ) /*: string*/
    {

        if ( ! $this->isValidColumn( $table, $column ) ) {
            throw new InvalidColumn("Column '{$column}' does not exist in the database table '{$table}' . ");
        }
        return $column;
    }
    
    public function isPrimaryKey( /*string*/ $column, /*string*/ $table ) /*: bool*/ 
    {
        $column = $this->validColumn( $this->validTable($table), $column );
        return !empty($this->columns[$table][$column]->primary);
    }
    
    public function getPrimaryKeyName( /*string*/ $table ) /*: string*/
    {
        $table = $this->validTable($table);
        
        foreach( (array) $this->columns[$table] as $column ) {
            if ( !empty($column->primary) ) { return $column->name; }
        }

        throw new PrimaryKeyNotFound("No primary key found for table {$table}. ");
    }
    
}
