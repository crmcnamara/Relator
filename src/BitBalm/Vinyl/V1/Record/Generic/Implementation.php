<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record\Generic;

use BitBalm\Vinyl\V1 as Vinyl;


Trait Implementation /* implements Vinyl\Record */
{
    public $record_id;
    public $record_values = [];
    public /*Record*/ $moved_to;
    
    
    public function withValues( $record_id, array $values ) : Vinyl\Record
    {
        // If the called record is a not prototype, and an insertable prototype was not requested,
        //    set the values on the called record.
        if ( isset( $this->record_id, $record_id ) ) { 
            $this->setValues($values); 
        }
        
        // If this record was moved in the past, forward all calls to that record instance. 
        if ( $this->moved_to ) { 
            return $this->moved_to->withValues( $record_id, $values ); 
        }
        
        $record = $this;
        
        // If our internal id is not set, we're a prototype. 
        // If the passed id is not set, an insertable prototype is being requested.
        if ( ! isset( $this->record_id, $record_id ) ) { 
          $record = clone $this;
        }
        
        // if both ids are set, but don't match, our id is being moved. 
        elseif ( $this->record_id != $record_id ) { 
            $record = $this->moved_to = clone $this;
        }

        $record->record_id = $record_id;
        $record->setValues($values);
        
        return $record;
    }
    
    protected function setValues( array $values )
    {
        $this->record_values = $values;
    }
    
    public function getAllValues() : array 
    {
        return $this->record_values;
    }
    
    public function getRecordId()
    {
        if ( $this->moved_to ) { return $this->moved_to->getRecordId(); }
        return $this->record_id;
    }
    
    public function getUpdatedValues() : array
    {
        return $this->getAllValues();
    }
    
    


}
