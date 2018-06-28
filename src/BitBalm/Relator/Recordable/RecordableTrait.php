<?php

namespace BitBalm\Relator\Recordable;

use Exception;

use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recorder;


Trait RecordableTrait
{
    use RecordTrait;
    
    
    protected static $recorders ;
    protected $recorder_loaded_id;
    
    
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
    
    protected function setLoadedId( $id ) : Recordable
    {
        if ( $id === $this->recorder_loaded_id ) { return $this ; }
        
        if ( ! is_null($this->recorder_loaded_id) ) {
            throw new Exception("This record's loaded id is already set. ");
        }
        
        $this->recorder_loaded_id = $id ;
        
        return $this;
    }
    
    public function getLoadedId() 
    {
        return $this->recorder_loaded_id ?? null ;
    }
    
    public function createFromArray( array $values ) : Record
    {
        $record = new static;
        $record->record_values = $values ;
        $record->setLoadedId( $values[ $this->getPrimaryKeyName() ] ?? null );
        return $record;
    }
    
}
