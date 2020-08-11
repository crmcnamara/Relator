<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection\Records;

use PDOStatement;

use OuterIterator;
use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Collection\Records;
use BitBalm\Vinyl\V1\RecordProducer\PDO\Statement;



class PDO extends Vinyl\Collection\Records implements Vinyl\RecordProducer\PDO
{
    protected /* Statement */ $producer;
    protected /* Vinyl\Record */ $record;
    
    
    public function __construct( Statement $producer, int $flags = 0  )
    {
        $this->producer = $producer;
        parent::__construct( [], $flags );
    }
    
    public function withStatement( PDOStatement $statement, string $id_field = 'id' ) : Vinyl\RecordProducer\PDO
    {
        $producer = $this->producer->withStatement( $statement, $id_field );
        $collection = clone $this;
        foreach ( array_keys( $collection->getArrayCopy() ) as $key ) {
            $collection->offsetUnset($key);
        }
        foreach ( $producer as $key => $record ) {
            $collection->offsetSet( $key, $record );
        }
        
        return $collection;
    }
    
    public function withRecord( Vinyl\Record $prototype ) : Vinyl\RecordProducer\PDO
    {
        $collection = clone $this;
        $collection->producer = $this->producer->withRecord($prototype);
        return $collection;
    }
    
    public function getInnerIterator()
    {
        return $this->producer;
    }
    
    public function getMasterRecord() : Vinyl\Record
    {
        return $this->producer->getMasterRecord();
    }
}
