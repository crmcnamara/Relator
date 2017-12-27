<?php

namespace BitBalm\Relator;

class SimpleRelationshipSet implements RelationshipSet
{
    protected $record ;
    protected $relationships ;
    protected $relationship ;
    
    public function __construct( Record $record, Relator $relator, Relationship $relationship = null )
    {
        $record->setRelator($relator);
        $record->setRelationships($this);
        $this->record = $record ;
    }
    
    public function addRelationship( Relationship $relationship, string $name = null ) : RelationshipSet
    {
        
        if ( empty($name) ) { $name = $relationship->getToTable()->getTableName(); }
        
        $fromTableName = $relationship->getFromTable()->getTableName();
        
        if ( $relationship === $this->relationships[$fromTableName][$name] ) { 
            return $this ; 
        }
        
        if ( $this->relationships[$fromTableName][$name] instanceof Relationship ) {
            throw new InvalidArgumentException(
                "A relationship from {$fromTableName} to {$name} is already set. "
              );
        }
        
        if ( ! ( 
            get_class( $relationship->getFromTable() ) === get_class( $this->record )
                and
            $relationship->getFromTable()->getTableName() === $this->record->getTableName()
          ) ) {
            throw new InvalidArgumentException(
                "The given Relationship's 'from' table does not match the one for this RelationshipSet. ");
        }
        
        $this->relationships[$fromTableName][$name] = $relationship;
        
        return $this;
    }
    
    public function setRelationship( string $fromColumn, Record $toTable, string $toColumn, $name = null ) : Relationship
    {
        $relationshipType = $this->relationship ?? SimpleRelationship::class ;
        $relationship = new $relationshipType( $this->record, $fromColumn, $toTable, $toColumn );
        $this->addRelationship( $relationship, is_string($name) ? $name : null );
        return $relationship ;
    }
    
    public function addRelationships( array $relationships ) : RelationshipSet
    {
        foreach ( $relationships as $name => $relationship ) {
          
            if ( is_array ($relationship) ) {
                $relationship = $this->setRelationship( 
                    array_shift($relationship), 
                    array_shift($relationship), 
                    array_shift($relationship), 
                    is_string($name) ? $name : null 
                  );
                
            } elseif ( $relationship instanceof Relationship ) {
                $this->addRelationship( $relationship, is_string($name) ? $name : null );
                
            } else {
                throw new InvalidArgumentException("Invalid relationship argument for {$name}. ") ;
            }
            
        }
        
        return $this;
    }
    
    public function getRelationship( string $fromTableName, string $relationshipName ) : Relationship
    {
        if ( $relationship = $this->relationships[$fromTableName][$relationshipName] ) {
            return $relationship;
        }
        throw new Exception("Relationship from $fromTableName to $relationshipName not found. ");
    }
    
    
}
