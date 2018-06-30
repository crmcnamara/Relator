<?php

namespace BitBalm\Relator\Relator;

#use PDO;
use Exception;
use InvalidArgumentException;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Record;

use PDOStatement;


class PDO extends BaseMapper implements Relator
{
    
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet
    {
        $statement = $this->getRelatedStatement( $relationship, $recordset );
        $statement->execute();
        $results = $statement->fetchAll();

        foreach ( $results as $index => $result ) {
            if ( ! $result instanceof Record ) {
                $results[$index] = $relationship->getToTable()->newRecord()->setValues($result);
            }
        }

        $resultset = new $recordset( $results );

        return $resultset;
        
    }
    
    public function getRelatedStatement( Relationship $relationship, RecordSet $recordset ) : PDOStatement
    {
        $toTable  = $relationship->getToTable();
        $toTableName = $this->getValidator()->validTable($toTable->getTableName());
        $toColumn = $this->getValidator()->validColumn( $toTableName, $relationship->getToColumn() );
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
        
        $statement = $this->pdo->prepare( $querystring );
        
        foreach ( $values as $index => $value ) {
            $statement->bindValue( $index+1, $value );
        }

        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        
        return $statement;
    }
    
}
