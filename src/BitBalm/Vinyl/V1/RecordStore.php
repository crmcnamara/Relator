<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface RecordStore
{
    public function getRecord( $record_id ) : Record ;
    public function getRecords( array $record_ids ) : Collection\Records ;
    public function getRecordByFieldValues( string $field, $value ) : Record ; 
    public function getRecordsByFieldValues( string $field, array $values ) : Collection\Records ;
    
    public function insertRecord( array $values ) : Record ;
    public function updateRecord( Record $record ) : Record ;
    public function deleteRecord( Record $record );
}
