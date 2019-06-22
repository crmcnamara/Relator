<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl;


interface Relationship
{
    public function sourceClass() : string ;
    public function sourceField();
    public function destinationRecordStore() : RecordStore ;
    public function destinationField();
}
