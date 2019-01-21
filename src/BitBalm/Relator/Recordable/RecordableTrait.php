<?php

namespace BitBalm\Relator\Recordable;

use Exception;
use InvalidArgumentException;

use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\Exception\RecorderAlreadySet;
use BitBalm\Relator\Exception\RecordNotFound;



Trait RecordableTrait
{    
    protected static $primary_key_name;
    protected static $recorder;
    
    protected $recorder_update_id;
    
    
    public function setRecorder( Recorder $recorder ) /*: Recordable*/
    {
        if ( self::$recorder and self::$recorder !== $recorder ) {
            throw new RecorderAlreadySet("This record's Recorder is already set. ");
        }
        
        self::$recorder = $recorder;
        
        return $this;
    }
    
    public function getRecorder() /*: Recorder*/ 
    {
        #TODO: throw Exception instead of TypeError when not set?
        return self::$recorder;
    }
    
    
    public function loadRecord( $record_id ) /*: Recordable*/ 
    {
        return $this->getRecorder()->loadRecord( $this, $record_id );
    }

    public function loadRecords( array $record_ids ) /*: RecordSet*/ 
    {
        return $this->getRecorder()->loadRecords( $this, $record_ids );
    }
    
    public function saveRecord( $update_id = null ) /*: Recordable*/ 
    {
        return $this->getRecorder()->saveRecord( $this, $update_id );
    }

    public function insertRecord() /*: Recordable*/ 
    {
        return $this->getRecorder()->insertRecord($this);
    }
        
    public function updateRecord( $update_id ) /*: Recordable*/ 
    {
        return $this->getRecorder()->updateRecord( $update_id, $this );
    }

    public function deleteRecord() 
    {
        return $this->getRecorder()->deleteRecord($this);
    }
    
    
    public function setUpdateId( $id ) /*: Recordable*/
    {
        $this->recorder_update_id = $id ;
        return $this;
    }
    
    public function getUpdateId() 
    {
        $update_id = $this->recorder_update_id;
        
        // and, lastly, the id set in the record's values. 
        if ( $update_id === null ) { 
            $values = $this->asArray(); 
            $prikey = $this->getPrimaryKeyName();
            if ( array_key_exists( $prikey, $values ) ) {
                $update_id = $values[$prikey]; 
            }
        }
      
        return $update_id;
    }
    
    public function getPrimaryKeyName() /*: string*/
    {
        return $this->getRecorder()->getPrimaryKeyName( $this->getTableName() );
    }
    
    public function loadValues( $record_id ) /*: ?array*/
    {
        try {
            return $this->loadRecord( $record_id )->asArray();
            
        } catch ( RecordNotFound $e ) {
            return null;
        }
    }
}
