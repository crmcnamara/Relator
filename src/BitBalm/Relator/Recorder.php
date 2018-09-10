<?php

namespace BitBalm\Relator;


interface Recorder
{
    public function loadRecord( Recordable $record, $record_id ) : Recordable ;
    
    public function loadRecords( Recordable $record, array $record_ids ) : RecordSet ;
    
    public function saveRecord( Recordable $record, $record_id = null ) : Recordable ;
    
    public function insertRecord( Recordable $record ) : Recordable ;
    
    public function updateRecord( $record_id, Recordable $record ) : Recordable ;
    
    public function deleteRecord( Recordable $record ) ;
    
    public function getPrimaryKeyName( string $table_name ) : string ;
}
