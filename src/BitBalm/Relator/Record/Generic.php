<?php

namespace BitBalm\Relator\Record;

use Exception;
use InvalidArgumentException;
use ArrayObject;

use BitBalm\Relator\Mappable;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;


abstract class Generic extends ArrayObject implements Mappable, Recordable, Relatable, GetsRelatedRecords
{
    
    public function asArray() : array
    {
        return $this->getArrayCopy();
    }
    
    public function setValues( array $values ) : Mappable 
    {
        $this->exchangeArray(array_replace( (array) $this->getArrayCopy(), $values )) ;
        return $this;
    }
    
}
