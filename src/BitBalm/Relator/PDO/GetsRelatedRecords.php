<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\GetsRelatedRecords as BaseInterface;

Interface GetsRelatedRecords extends BaseInterface
{
    
    /**
     * @return string sql table name
     */
    public function getTableName();
    
}
