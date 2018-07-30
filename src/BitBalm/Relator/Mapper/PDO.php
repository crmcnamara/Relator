<?php

namespace BitBalm\Relator\Mapper;

#use PDO;

use BitBalm\Relator\Mapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Mapper\PDO\SchemaValidator;


class PDO implements Mapper
{
    use Recorder\PDOTrait, Relator\PDOTrait;
    
    
    protected $pdo ;
    protected $validator;    
    
    
    public function __construct( \PDO $pdo, SchemaValidator $validator ) 
    {
        $this->pdo = $pdo;
        $this->validator = $validator;
    }
    
    public function getValidator() 
    {
        return $this->validator;
    }
}
