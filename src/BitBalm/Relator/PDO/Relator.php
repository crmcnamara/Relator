<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\Relator as RelatorInterface;
use BitBalm\Relator\BaseRelator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\GenericRecord;



use PDO;
use PDOStatement;


class Relator extends BaseRelator implements RelatorInterface
{
    
    protected $pdo ;
    
    public function __construct( PDO $pdo ) 
    {
        $this->pdo = $pdo;
    }
        
    public function getPDO() : PDO
    {
        return $this->pdo;
    }
    
    public function getRelated( Relationship $relationship, RecordSet $recordset ) : RecordSet
    {
        $statement = $this->getRelatedStatement( $relationship, $recordset );
        $statement->execute();
        $results = $statement->fetchAll();
        
        $resultset = new $recordset($results);
        if ( empty( $resultset ) ) { 
            $resultset->relatorTable = $relationship->getToTable()->getTable(); 
        }
        foreach ( $resultset as $record ) {
            $record->setRelator($this);
        }
        
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
        
        $fetchmode = [ PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, get_class( $toTable ), ] ;
        // Normally, table names for classes are stored statically per-class
        // GenericRecords, however, must store them individually in each instance
        // Thus, we must pass them through here as a constructor argument
        #TODO: Are we cheating? should we be implementing getFetchMode() on extended interfaces/implementations?
        if ( $relationship->getToTable() instanceof GenericRecord ) {
            $fetchmode[] = [ $relationship->getToTable()->getTableName(), $relationship->getToTable() ] ;
        }
        
        $statement->setFetchMode( ...$fetchmode );
        
        return $statement;
    }
    
}
