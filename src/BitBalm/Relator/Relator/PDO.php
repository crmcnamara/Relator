<?php

namespace BitBalm\Relator\Relator;

use Exception;
use InvalidArgumentException;
#use PDO;
use PDOStatement;

use Aura\SqlSchema\SchemaInterface;

use BitBalm\Relator\PDO\BaseMapper;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Record;
use BitBalm\Relator\Mappable;


class PDO extends BaseMapper implements Relator
{
    
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet
    {
        $statement = $this->getRelatedStatement( $relationship, $recordset );
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
    
    public function getRelatedStatement( Relationship $relationship, RecordSet $recordset ) : PDOStatement
    {
        $to_table  = $relationship->getToTable();
        $to_table_name = $this->getValidator()->validTable($to_table->getTableName());
        $to_column = $this->getValidator()->validColumn( $to_table_name, $relationship->getToColumn() );
        $querystring = "SELECT * from {$to_table_name} where {$to_column} in ( ? ) ";
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
