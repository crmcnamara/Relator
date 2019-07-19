<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface Record
{
    public function initializeRecord( $record_id, array $values );
    public function getRecordId();
    public function getUpdatedValues() : array ;
    public function getAllValues() : array ;
}
