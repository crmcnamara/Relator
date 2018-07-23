<?php

namespace BitBalm\Relator\Relator;

use Exception;
use InvalidArgumentException;
use PDO;
use PDOStatement;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Record;
use BitBalm\Relator\Mappable;


trait PDOTrait 
{
    
    public function getRelated( GetsRelatedRecords $related_from, Relationship $relationship  ) : RecordSet
    {
        $statement = $this->getRelatedStatement( $related_from, $relationship );
        $statement->execute();
        $results = $statement->fetchAll();

        foreach ( $results as $index => $result ) {
            if ( ! $result instanceof Mappable ) {
                $results[$index] = $relationship->getToTable()->newRecord()->setValues($result);
            }
        }
        
        $to_recordset = $relationship->getToTable()->asRecordSet();
        
        $resultset = new $to_recordset( $results );

        return $resultset;
        
    }
    
    public function getRelatedStatement( GetsRelatedRecords $related_from, Relationship $relationship ) : PDOStatement
    {
        $to_table  = $relationship->getToTable();
        $to_table_name = $this->getValidator()->validTable($to_table->getTableName());
        $to_column = $this->getValidator()->validColumn( $to_table_name, $relationship->getToColumn() );
        $querystring = "SELECT * from {$to_table_name} where {$to_column} in ( ? ) ";
        
        $values = array_column( $related_from->asRecordSet()->asArrays(), $relationship->getFromColumn() );
        
        // Replace the placeholder with as many placeholders as we have values
        $querystring = str_replace( 
            '?', 
            implode( ', ', array_pad( [], count( $values ), '?' ) ), 
            $querystring 
          );
        
        $statement = $this->pdo->prepare( $querystring );
        
        foreach ( $values as $index => $value ) {
            $statement->bindValue( $index+1, $value );
        }

        $statement->setFetchMode(PDO::FETCH_ASSOC);
        
        return $statement;
    }
    
}
