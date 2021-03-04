<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record\GetsRelatives;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;


trait Implementation /* implements Vinyl\GetsRelatives */
{
    abstract public function getRelator(): Vinyl\Relator ;
    
    abstract protected function getSourceRecord() : Vinyl\Record;
    
    
    public function getRelative( string $relationship_name ) : Vinyl\Record 
    {
        $record = $this->getSourceRecord();

        $relationship = $this->getRelator()->getRelationship( $record, $relationship_name );

        $relative = $relationship->destinationRecordStore()
            ->getRecordByFieldValue(
                $relationship->destinationField(), 
                $record->getAllValues()[ $relationship->sourceField() ]
              );
              
        return $relative;
    }
    
    public function getRelatives( string $relationship_name ) : Vinyl\RecordProducer 
    {
        $record = $this->getSourceRecord();

        $relationship = $this->getRelator()->getRelationship( $record, $relationship_name );

        $records = $relationship->destinationRecordStore()
            ->getRecordsByFieldValues(
                $relationship->destinationField(), 
                [ $record->getAllValues()[ $relationship->sourceField() ] ]
              );
              
        return $records;
    }
}
