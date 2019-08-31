<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;

use ArrayObject;
use ArrayAccess;


use BitBalm\Vinyl\V1 as Vinyl;


class Generic extends ArrayObject implements Vinyl\Record, ArrayAccess
{
    use GenericImplementation;
    
    
    public function initializeRecord( $record_id, array $values )
    {
        $this->exchangeArray($values);
        $this->record_id = $record_id;
        return $this;
    }
    
    public function getAllValues() : array 
    {
        return (array) $this;
    }
    
}
