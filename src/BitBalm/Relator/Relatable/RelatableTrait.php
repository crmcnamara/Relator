<?php

namespace BitBalm\Relator\Relatable;

use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;


Trait RelatableTrait
{
    use GetsRelatedTrait;
    
    protected static $relators ;
    protected static $relationships ;
    
    
    public function setRelator( Relator $relator ) : Record
    {
        $existing = isset( self::$relators[ $this->getTableName() ] ) 
            ? self::$relators[ $this->getTableName() ] : null ;
            
        if ( $relator === $existing ) { return $this ; }
        
        if ( $existing instanceof Relator ) {
            throw new InvalidArgumentException("This record's Relator is already set. ");
        }
            
        self::$relators[ $this->getTableName() ] = $relator;
        
        return $this;
    }
    
    public function getRelator() : Relator 
    {
        $existing = isset( self::$relators[ $this->getTableName() ] )
            ? self::$relators[ $this->getTableName() ] : null ;
            
        if ( $existing instanceof Relator ) { return $existing ; }
        
        throw new Exception( "This record's Relator is not yet set. ");
    }

    public function addRelationship( 
        string $fromColumn, 
        Record $toTable, 
        string $toColumn, 
        string $relationshipName = null 
      ) : Record
    {
        $this->setRelationship( 
            new Relationship\Simple( $this, $fromColumn, $toTable, $toColumn ),
            $relationshipName 
          );
          
        return $this ;
    }
    
    public function setRelationship( Relationship $relationship, string $relationshipName = null ) : Record
    {
        
        if ( empty($relationshipName) ) { $relationshipName = $relationship->getToTable()->getTableName(); }
        
        $fromTableName = $relationship->getFromTable()->getTableName();
        
        $existing = isset( self::$relationships[$fromTableName][$relationshipName] )
            ? self::$relationships[$fromTableName][$relationshipName] : null ;
            
        if ( $relationship === $existing ) { return $this ; }
        
        if ( $existing instanceof Relationship ) {
            throw new InvalidArgumentException(
                "A relationship to {$relationshipName} is already set. "
              );
        }
        
        if ( ! ( $relationship->getFromTable() instanceof $this ) ) {
            throw new InvalidArgumentException(
                "The given Relationship's fromTable must be an instance of ". self::class .". "
              );
        }
        
        if ( $fromTableName !== $this->getTableName() ) {
            throw new InvalidArgumentException(
                "The given Relationship's fromTable must have a tableName of {$fromTableName}. "
              );
        }
        
        self::$relationships[$fromTableName][$relationshipName] = $relationship;
        
        return $this;
    }
    
    public function getRelationship( string $relationshipName ) : Relationship
    {
        if ( isset(self::$relationships[ $this->getTableName() ][$relationshipName]) ) { 
            return self::$relationships[ $this->getTableName() ][$relationshipName] ; 
        }
        throw new Exception("A relationship to {$relationshipName} is not set. ");
    }
    


}
