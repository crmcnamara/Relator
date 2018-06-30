<?php

namespace BitBalm\Relator\PDO;

use PDO;
use Exception;
use InvalidArgumentException;
use BitBalm\Relator\PDO\SchemaValidator;


/** This serves as the common basis for the Relator\PDO and Recorder\PDO implementations
 */
abstract class BaseMapper
{
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
