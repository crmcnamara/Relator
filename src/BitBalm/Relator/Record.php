<?php

namespace BitBalm\Relator;


interface Record extends Mappable, Recordable, Relatable, GetsRelatedRecords
{
    public function setMapper( Mapper $mapper ) : Record ;
}
