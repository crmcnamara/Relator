<?php

namespace BitBalm\Relator\PDO;

use PDO;
use BitBalm\Relator\RecordTrait as BaseRecordTrait;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\SimpleRecordSet;

Trait RecordTrait {
  
    use BaseRecordTrait;
    
    public function getFetchMode()
    {
        return [ PDO::FETCH_CLASS, $this ] ;
    }
    
}
