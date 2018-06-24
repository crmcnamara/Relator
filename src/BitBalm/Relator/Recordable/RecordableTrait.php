<?php

namespace BitBalm\Relator\Recordable;

use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recorder;


Trait RecordableTrait
{
    protected static $recorders ;
    protected static $recorder_loaded_id;
    
    
    public function setRecorder( Recorder $recorder ) : Recordable
    {
        $existing = self::$recorders[ $this->getTableName() ] ?? null ;
            
        if ( $recorder === $existing ) { return $this ; }
        
        if ( $existing instanceof Recorder ) {
            throw new InvalidArgumentException("This record's Recorder is already set. ");
        }
            
        self::$recorders[ $this->getTableName() ] = $recorder;
        
        return $this;
    }
    
    public function getRecorder() : Recorder 
    {
        $existing = self::$recorders[ $this->getTableName() ] ?? null ;
            
        if ( $existing instanceof Recorder ) { return $existing ; }
        
        throw new Exception( "This record's Recorder is not yet set. ");
    }
    
    public function getLoadedId() 
    {
        return $this->recorder_loaded_id ?? null ;
    }
    
    public function loadRecord( $record_id ) : Recordable 
    {
        return $this->getRecorder()->loadRecord( $this, $record_id );
    }
    
    public function saveRecord() : Recordable 
    {
        return $this->getRecorder()->saveRecord($this);
    }
    
    public function deleteRecord() 
    {
        return $this->getRecorder()->deleteRecord($this);
    }
    
    public function createFromArray( array $values ) : Record
    {
        $record = new static;
        $record->record_values = $values ;
        
        $this->recorder_loaded_id = $values[ $this->getPrimaryKeyName() ] ?? null ;
        
        return $record;
    }
    
}
