<?php

namespace BitBalm\Relator;


interface Recordable extends Mappable
{
    public function getPrimaryKeyName() /*: string*/ ;
    
    public function setRecorder( Recorder $recorder ) /*: Recordable*/ ;
    
    public function getRecorder() /*: Recorder*/ ;
    
    public function loadRecord( $record_id ) /*: Recordable*/ ;
    
    public function loadRecords( array $record_ids ) /*: RecordSet*/ ;
    
    public function saveRecord() /*: Recordable*/ ;
    
    public function deleteRecord() ;
    
    public function setUpdateId( $id ) /*: Recordable*/ ;
    
    public function getUpdateId() ;
    
    public function asRecordSet() /*: RecordSet*/ ;
}
