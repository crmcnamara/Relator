<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\Relator as RelatorInterface;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\BaseRelator;


use PDO;


class Relator extends BaseRelator implements RelatorInterface
{
    
    protected $pdo ;
    
    public function __construct( PDO $pdo ) 
    {
        $this->pdo = $pdo;
    }
        
    /**
     * @return PDO
     */
    public function getPDO() 
    {
        return $this->pdo;
    }
    
    public function getRelated( Relationship $relationship, RecordSet $records )
    {
    }
    
}
