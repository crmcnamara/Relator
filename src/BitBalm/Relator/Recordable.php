<?php

namespace BitBalm\Relator;


interface Recordable extends Mappable
{
    public function getPrimaryKeyName() : string ;
    
    public function setRecorder( Recorder $recorder ) : Recordable ;
    
    public function getRecorder() : Recorder ;
    
    public function loadRecord( $record_id ) : Recordable ;
    
    public function loadRecords( array $record_ids ) : RecordSet ;
    
    public function saveRecord( $record_id = null ) : Recordable ;
    
    public function updateRecord( $record_id ) : Recordable ;
    
    public function insertRecord() : Recordable ;
    
    public function deleteRecord() ;
    
    public function setUpdateId( $record_id ) : Recordable ;
    
    public function getUpdateId() ;
    
    public function asRecordSet() : RecordSet ;
}
