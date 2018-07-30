<?php

namespace BitBalm\Relator\Record;

use BitBalm\Relator\Mapper;
use BitBalm\Relator\Record;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\GetsRelatedRecords\GetsRelatedTrait;


/* A trait for records/models/entities 
 *    that implements Mappable, Recordable, Relatable, and GetRelatedRecords interfaces
 */
Trait RecordTrait
{
    use MappableTrait, RecordableTrait, RelatableTrait, GetsRelatedTrait
    {
        GetsRelatedTrait::asRecordSet insteadof MappableTrait;
    }
    
    public function setMapper( Mapper $mapper ) /*: Record*/
    {
        $this->setRelator($mapper);
        $this->setRecorder($mapper);
        
        return $this;
    }
}
