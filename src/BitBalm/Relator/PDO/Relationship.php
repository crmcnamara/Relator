<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\Relationship as BaseRelationship;

Interface Relationship extends BaseRelationship
{
    
    public function getFromTable();
    public function getFromColumn();
    public function getToTable();
    public function getToColumn();
    
    /** 
     * @return array of parameters suitable for input to PDOStatement::setFetchMode()
     */
    public function getFetchMode();
    
}
