<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection\Records\PDO;

use OuterIterator;
use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Collection\Records;
use BitBalm\Vinyl\V1\Relator;
use BitBalm\Vinyl\V1\RecordProducer\PDO\Statement;


class GetsRelatives extends Vinyl\Collection\Records\PDO implements Vinyl\GetsRelatives
{
    use Records\GetsRelatives\Implementation;
    
    
    protected /* Vinyl\Relator */ $relator;
    
    
    public function __construct( Relator $relator, Statement $producer, int $flags = 0  )
    {
        $this->relator = $relator;
        parent::__construct( $producer, $flags );
    }
    
    protected function getRelator() : Vinyl\Relator
    {
        return $this->relator;
    }
    
    protected function getRecordProducer() : Vinyl\RecordProducer
    {
        return $this;
    }
}
