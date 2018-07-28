<?php

namespace BitBalm\Relator\Relatable;

use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\AlreadySetException;


class RelatorAlreadySet extends InvalidArgumentException implements AlreadySetException {}


Trait RelatableTrait
{    
    protected static $relator ;
    
    
    public function setRelator( Relator $relator ) : Relatable
    {
        if ( self::$relator and self::$relator !== $relator ) {
            throw new RelatorAlreadySet("This record's Relator is already set. ");
        }
        
        self::$relator = $relator;
        
        return $this;
    }
    
    public function getRelator() : Relator 
    {
        #TODO: throw Exception instead of TypeError when not set?
        return static::$relator;
    }
    
}
