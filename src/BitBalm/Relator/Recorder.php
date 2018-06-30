<?php

namespace BitBalm\Relator;


interface Recorder
{
    public function loadRecord( Recordable $record, $record_id ) : Recordable ;
    
    public function loadRecords( Recordable $record, array $record_ids ) : RecordSet ;
    
    public function saveRecord( Recordable $record ) : Recordable ;
    
    public function deleteRecord( Recordable $record ) ;
}
