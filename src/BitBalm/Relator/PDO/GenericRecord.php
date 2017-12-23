<?php

namespace BitBalm\Relator\PDO;

use Exception;
use InvalidArgumentException;
use PDO;
use ArrayObject;

use BitBalm\Relator\GenericRecord as BaseRecord;

final class GenericRecord extends BaseRecord
{
    use RecordTrait;
    
    public function getFetchMode() 
    {
        return [ PDO::FETCH_CLASS, $this, [ $this->getTable() ], ] ;
    }
    
}
