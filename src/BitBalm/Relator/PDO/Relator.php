<?php

namespace BitBalm\Relator\PDO;

use BitBalm\Relator\Relator as RelatorInterface;
use BitBalm\Relator\BaseRelator;
use BitBalm\Relator\Relationship;
use BitBalm\Relator\RecordSet;



use PDO;


class Relator extends BaseRelator implements RelatorInterface
{
    
    protected $pdo ;
    
    public function __construct( PDO $pdo ) 
    {
        $this->pdo = $pdo;
    }
        
    public function getPDO() : PDO
    {
        return $this->pdo;
    }
    
    public function getRelated( Relationship $relationship, RecordSet $records ) : RecordSet
    {
    }
    
}
