<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;

use ArrayObject;
use ArrayAccess;


use BitBalm\Vinyl\V1 as Vinyl;


class Generic extends ArrayObject implements Vinyl\Record, ArrayAccess
{
    use Generic\Implementation;
    
    
    protected function setValues( array $values )
    {
        $this->exchangeArray($values);
    }
    
    public function getAllValues() : array 
    {
        return (array) $this;
    }
    
}
