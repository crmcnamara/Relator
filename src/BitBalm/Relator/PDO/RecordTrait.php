<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\RecordTrait as BaseRecordTrait;

Trait RecordTrait {
  
    use BaseRecordTrait;
    
    abstract public function getTableName() ;
    
    public function getFetchMode()
    {
        return [ PDO::FETCH_CLASS, $this ] ;
    }
    
    public function asRecordSet()
    {
        return new SimpleRecordSet([ $this ]);
    }
    
}
