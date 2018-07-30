<?php

namespace BitBalm\Relator\GetsRelatedRecords;

use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\RecordSet\GetsRelated;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\AlreadySetException;
use BitBalm\Relator\NotYetSetException;


class RelationshipAlreadySet extends InvalidArgumentException implements AlreadySetException {}

class InvalidRelationship extends InvalidArgumentException {}

class RelationshipNotYetSet extends InvalidArgumentException implements NotYetSetException {}


Trait GetsRelatedTrait 
{
    protected static $relationships;
    
    
    public function getRelated( /*string*/ $relationship_name ) /*: RecordSet*/
    {
        $relationship = $this->getRelationship( $relationship_name );
        
        $related = $relationship->getToTable()->getRelator()->getRelated( $this, $relationship ) ;
        
        return $related;
    }
    
    
    /* A convenience method not strictly required by GetsRelatedRecords */
    public function addRelationship( 
        /*string*/    $fromColumn, 
        Mappable  $toTable, 
        /*string*/    $toColumn, 
        /*string*/    $relationship_name = null 
      ) /*: GetsRelatedRecords*/
    {
        $this->setRelationship( 
            new Relationship\Simple( $this, $fromColumn, $toTable, $toColumn ),
            $relationship_name 
          );
          
        return $this ;
    }
    
    public function setRelationship( Relationship $relationship, /*string*/ $relationship_name = null ) /*: GetsRelatedRecords*/
    {
        
        if ( empty($relationship_name) ) { $relationship_name = $relationship->getToTable()->getTableName(); }
        
        $from_table_name = $relationship->getFromTable()->getTableName();
        
        $existing = isset( self::$relationships[$relationship_name] )
            ? self::$relationships[$relationship_name] : null ;
            
        if ( $relationship === $existing ) { return $this ; }
        
        if ( $existing instanceof Relationship ) {
            throw new RelationshipAlreadySet(
                "A relationship to {$relationship_name} is already set. "
              );
        }
        
        if ( ! ( $relationship->getFromTable() instanceof $this ) ) {
            throw new InvalidRelationship(
                "The given Relationship's from table must be an instance of ". self::class .". "
              );
        }
        
        if ( $from_table_name !== $this->getTableName() ) {
            throw new RelationshipAlreadySet(
                "The given Relationship's from table must have a table name of {$from_table_name}. "
              );
        }
        
        self::$relationships[$relationship_name] = $relationship;
        
        return $this;
    }
    
    public function getRelationship( /*string*/ $relationship_name ) /*: Relationship*/
    {
        if ( isset(self::$relationships[$relationship_name]) ) { 
            return self::$relationships[$relationship_name]; 
        }
        throw new RelationshipNotYetSet("A relationship to {$relationship_name} is not set. ");
    }
    
    public function asRecordSet( RecordSet $recordset = null ) /*: RecordSet*/
    {
        return $recordset ? new $recordset([ $this ]) : new RecordSet\GetsRelated([ $this ]);
    }
    
}
