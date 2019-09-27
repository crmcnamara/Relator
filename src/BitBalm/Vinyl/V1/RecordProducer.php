<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;

use Traversable;

interface RecordProducer extends \Iterator
{
    public function current() : Record ;
}
