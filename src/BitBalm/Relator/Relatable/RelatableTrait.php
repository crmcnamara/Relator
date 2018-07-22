<?php

namespace BitBalm\Relator\Relatable;

use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;


Trait RelatableTrait
{    
    protected static $relators ;
    protected static $relationships ;
    
    
    public function setRelator( Relator $relator ) : Relatable
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
    
}
