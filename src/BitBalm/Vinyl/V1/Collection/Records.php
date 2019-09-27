<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;

use Traversable;

use BitBalm\Vinyl\V1 as Vinyl;


class Records extends Vinyl\Collection implements Vinyl\RecordProducer
{
    public function __construct( array $items = [], int $flags = 0  )
    {
        parent::__construct( $items, $flags );
        $this->rewind();
    }
    
    public function validItem( $item ) : Vinyl\Record
    {
        return $item;
    }
    
    public function current() : Vinyl\Record
    {
        return $this->validItem( parent::current() );
    }
}
