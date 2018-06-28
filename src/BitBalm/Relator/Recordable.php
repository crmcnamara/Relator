<?php

namespace BitBalm\Relator;

interface Recordable extends Record
{
    public function getPrimaryKeyName() : string ;
    
    public function setRecorder( Recorder $recorder ) : Recordable ;
    
    public function getRecorder() : Recorder ;
    
    public function loadRecord( $record_id ) : Recordable ;
    
    public function saveRecord() : Recordable ;
    
    public function deleteRecord() ;
    
    public function loadFromArray( array $values ) : Recordable ;
    
    public function getLoadedId() ;
}
