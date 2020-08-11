<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;

use Iterator;


interface RecordProducer extends Iterator
{
    public function current() : Record;
    
    public function asArray() : array;
    
    public function asArrays() : array;
    
    public function getMasterRecord() : Record;
}
