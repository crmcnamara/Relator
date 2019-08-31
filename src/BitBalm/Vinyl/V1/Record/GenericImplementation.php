<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;

use BitBalm\Vinyl\V1 as Vinyl;


Trait GenericImplementation /* implements Vinyl\Record */
{
    private $record_id;
    private $record_values = [];
    
    
    public function initializeRecord( $record_id, array $values )
    {
        $this->record_id = $record_id;
        $this->record_values = $values;
        return $this;
    }
    
    public function getAllValues() : array 
    {
        return $this->record_values;
    }
    
    public function getRecordId()
    {
        return $this->record_id;
    }
    
    public function getUpdatedvalues() : array
    {
        return $this->getAllValues();
    }

}
