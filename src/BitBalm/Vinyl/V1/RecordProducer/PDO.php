<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordProducer;

use PDOStatement;
use BitBalm\Vinyl\V1 as Vinyl;


interface PDO extends Vinyl\RecordProducer
{
    public function withStatement( 
        PDOStatement $statement, 
        Vinyl\Record $prototype = null, 
        string $id_field = null 
      ) : Vinyl\RecordProducer\PDO ;
    
    public function getMasterRecord() : Vinyl\Record;
}