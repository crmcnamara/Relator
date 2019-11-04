<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordProducer;

use PDOStatement;
use BitBalm\Vinyl\V1 as Vinyl;


interface PDO extends Vinyl\RecordProducer
{
    public function withStatement( PDOStatement $statement, string $id_field = 'id' ) : Vinyl\RecordProducer\PDO ;
    
    public function withRecord( Vinyl\Record $prototype ) : Vinyl\RecordProducer\PDO ;
    
}
