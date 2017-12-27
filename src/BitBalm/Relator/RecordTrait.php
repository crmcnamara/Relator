<?php

namespace BitBalm\Relator;

use InvalidArgumentException;


Trait RecordTrait 
{
    
    protected static $relator ;
        
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

    public function asRecordSet( RecordSet $recordset = null ) : RecordSet
    {
        return $recordset ? new $recordset([$this]) : new SimpleRecordSet([ $this ]);
    }
    
}
