<?php

namespace BitBalm\Relator\PDO;


use Exception;
use InvalidArgumentException;
use PDO;

final class GenericRecord extends BaseRecord 
{
    
    protected $tableName ;
    
    /**
     * @parameter string $tableName
     */
    public function __construct( $tableName )
    {
        $this->setTable( $tableName ) ;
    }
    
    /**
     * @parameter string $tableName
     */
    protected function setTable( $tableName )
    {
      
        if ( !empty( $this->tableName ) ) {
            throw Exception('Table Name is already set');
        }
        
        $tableName = stringval( $tableName ) ;
        
        if ( empty( $tableName ) ) {
            throw InvalidArgumentException();
        }
        
        $this->tableName = $tableName ;
        
        return $this ;
        
    }
    
    public function getTableName() {
        return $this->tableName ;
    }
    
    public function getFetchMode() 
    {
        return [ PDO::FETCH_CLASS, $this, [ $this->getTable() ], ] ;
    }
    
}
