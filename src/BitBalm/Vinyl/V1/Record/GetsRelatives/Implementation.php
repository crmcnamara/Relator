<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record\GetsRelatives;

use BitBalm\Vinyl\V1 as Vinyl;


trait implementation /* implements Vinyl\GetsRelatives */
{
    abstract public function getRelator(): Vinyl\Relator ;
    
    public function getRelative( string $relationship_name ) : Vinyl\Record 
    {
        $relationship = $this->getRelator()->getRelationship( $this, $relationship_name );
        $records = $relationship->destinationRecordStore()
            ->getRecordByFieldValue(
                $relationship->destinationField(), 
                $this->getAllValues()[ $relationship->sourceField() ]
              );
        return $records;
    }
      
    public function getRelatives( string $relationship_name ) : Vinyl\RecordProducer 
    {
        $relationship = $this->getRelator()->getRelationship( $this, $relationship_name );
        $records = $relationship->destinationRecordStore()
            ->getRecordsByFieldValues(
                $relationship->destinationField(), 
                [ $this->getAllValues()[ $relationship->sourceField() ] ]
              );
        return $records;
    }
}
