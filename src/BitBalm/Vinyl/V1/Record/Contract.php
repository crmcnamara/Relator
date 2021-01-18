<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record;

/** 
 * A Record\Contract allows you to inject the Contract as a Record dependency,
 *    then set and get an actual Record object at a later time. 
 * It's similar to a promise pattern. 
 * This is mainly used to help resolve circular dependencies 
 *    between Relators, RecordStores, RecordProducers, and Record classes.
 */
class Contract extends Generic
{
    protected /* Record */ $record;
    
    
    public function setRecord( Record $record )
    {
        $this->record = $record;
    }
    
    public function getRecord() : Record
    {
        return $this->record;
    }
    
}
