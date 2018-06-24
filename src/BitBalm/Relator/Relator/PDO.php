<?php

namespace BitBalm\Relator\Relator;

use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Record;

use PDOStatement;


class PDO implements Relator
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
    
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet
    {
        $statement = $this->getRelatedStatement( $relationship, $recordset );
        $statement->execute();
        $results = $statement->fetchAll();

        foreach ( $results as $index => $result ) {
            if ( ! $results[$index] instanceof Record ) {
                $record = $relationship->getToTable()->createFromArray( (array) $results[$index], $statement );
                $results[$index] = $record;
            }
        }

        $resultset = new $recordset( $results );

        return $resultset;
        
    }
    
    public function getRelatedStatement( Relationship $relationship, RecordSet $recordset ) : PDOStatement
    {
        $toTable  = $relationship->getToTable();
        $toTableName = $toTable->getTableName();
        $toColumn = $relationship->getToColumn();
        $querystring = "SELECT * from {$toTableName} where {$toColumn} in ( ? ) ";
        $values = [];
        foreach ( $recordset as $record ) {
            $values[] = $record->asArray()[ $relationship->getFromColumn() ] ;
        }
        // Replace the placeholder with as many placeholders as we have values
        $querystring = str_replace( 
            '?', 
            implode( ', ', array_pad( [], count( $values ), '?' ) ), 
            $querystring 
          );
        
        $statement = $this->getPDO()->prepare( $querystring );
        
        foreach ( $values as $index => $value ) {
            $statement->bindValue( $index+1, $value );
        }

        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        return $statement;
    }
    
}
