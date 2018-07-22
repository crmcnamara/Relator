<?php

namespace BitBalm\Relator\GetsRelatedRecords;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\RecordSet\GetsRelated;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;


Trait GetsRelatedTrait 
{
    
    public function getRelated( string $relationshipName ) : RecordSet
    {
        $relationship = $this->getRelationship( $relationshipName );
        
        $related = $relationship->getToTable()->getRelator()
            ->getRelated( $relationship, $this->asRecordSet() ) ;
        
        return $related;
    }
    
    
    /* A convenience method not strictly required by GetsRelatedRecords */
    public function addRelationship( 
        string    $fromColumn, 
        Mappable  $toTable, 
        string    $toColumn, 
        string    $relationship_name = null 
      ) : GetsRelatedRecords
    {
        $this->setRelationship( 
            new Relationship\Simple( $this, $fromColumn, $toTable, $toColumn ),
            $relationship_name 
          );
          
        return $this ;
    }
    
    public function setRelationship( Relationship $relationship, string $relationship_name = null ) : GetsRelatedRecords
    {
        
        if ( empty($relationship_name) ) { $relationship_name = $relationship->getToTable()->getTableName(); }
        
        $from_table_name = $relationship->getFromTable()->getTableName();
        
        $existing = isset( self::$relationships[$from_table_name][$relationship_name] )
            ? self::$relationships[$from_table_name][$relationship_name] : null ;
            
        if ( $relationship === $existing ) { return $this ; }
        
        if ( $existing instanceof Relationship ) {
            throw new InvalidArgumentException(
                "A relationship to {$relationship_name} is already set. "
              );
        }
        
        if ( ! ( $relationship->getFromTable() instanceof $this ) ) {
            throw new InvalidArgumentException(
                "The given Relationship's from table must be an instance of ". self::class .". "
              );
        }
        
        if ( $from_table_name !== $this->getTableName() ) {
            throw new InvalidArgumentException(
                "The given Relationship's from table must have a table name of {$from_table_name}. "
              );
        }
        
        self::$relationships[$from_table_name][$relationship_name] = $relationship;
        
        return $this;
    }
    
    public function getRelationship( string $relationship_name ) : Relationship
    {
        if ( isset(self::$relationships[ $this->getTableName() ][$relationship_name]) ) { 
            return self::$relationships[ $this->getTableName() ][$relationship_name] ; 
        }
        throw new Exception("A relationship to {$relationship_name} is not set. ");
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\GetsRelated([ $this ]);
    }
    
}
