<?php

namespace BitBalm\Relator;

use InvalidArgumentException;


Trait RecordTrait 
{
    
    protected static $relator ;
    protected static $relationships ;
        
    use GetsRelatedTrait;
    
    public function setRelator( Relator $relator ) : Record
    {
        if ( $relator === self::$relator ) { return $this ; }
        if ( self::$relator instanceof Relator ) {
            throw new InvalidArgumentException("This record's Relator is already set. ");
        }
            
        self::$relator = $relator;
        
        return $this;
    }
    
    public function getRelator() : Relator 
    {
        if ( self::$relator instanceof Relator ) { return self::$relator ; }
        throw new Exception( "This record's Relator is not yet set. ");
    }

    public function setRelationships( RelationshipSet $relset ) : Record
    {
        if ( $relset === self::$relationships ) { return $this ; }
        if ( self::$relationships instanceof RelationshipSet ) {
            throw new InvalidArgumentException("This record's RelationshipSet is already set. ");
        }
            
        $this->relationships = $relset;
        return $this;
    }
    
    public function getRelationship( string $relationshipName ) : Relationship
    {
        return $this->relationships->getRelationship( $this->getTableName(), $relationshipName );
    }
    
    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([$this]) : new SimpleRecordSet([ $this ]);
    }
    
}
