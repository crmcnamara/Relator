<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;


interface RecordStore
{   
    /**
     * Returns a new RecordStore instance 
     *    using the given Record as the template for Records produced by the other methods
     */
    public function withRecord( Record $prototype_record ) : RecordStore ;
    
    /** 
     * Returns a single record identified by its primary key id.
     * Throws RecordNotFound when it comes up empty. 
     */
    public function getRecord( $record_id ) : Record ;
    
    /**
     * Returns a RecordProducer iterator with zero or more Records 
     *    identified by any one of the passed primary key ids.
     * If called without any arguments at all, returns all Records in the Store
     */
    public function getRecords( array $record_ids = null ) : RecordProducer ;
    
    /**
     * Returns a single record 
     *    uniquely identified by having the given value in the given field.
     * Throws RecordNotFound when it comes up empty. 
     * Throws TooManyRecords if more than one record matches.
     * Throws InvalidArgumentException if the field is not valid for the record type.
     */
    public function getRecordByFieldValue( string $field, $value ) : Record ; 
    
    /**
     * Returns a RecordProducer iterator with zero or more Records 
     *    having any of the given values in the given field.
     * Throws InvalidArgumentException if the field is not valid for the record type.
     */
    public function getRecordsByFieldValues( string $field, array $values ) : RecordProducer ;
    
    
    /**
     * Creates a record in the source database with the given field-value pairs.
     * Returns a new Record object initialized from the result.
     */
    public function insertRecord( array $values ) : Record ;
    
    /**
     * Updates the field-value pairs of the record in the source database
     *    with the updated values on the Record object.
     * Implementors should target the source database record using Record::getRecordId()
     * Returns the same Record object after re-initializing it from the result.
     * Throws RecordNotFound when it can't find the target record in the source database.
     */
    public function updateRecord( Record $record ) : Record ;
    
    /**
     * Deletes the record identified by the passed Record object from the source database.
     * Implementors should target the source database record using Record::getRecordId()
     * Throws RecordNotFound when it can't find the target record in the source database.
     */
    public function deleteRecord( Record $record );
}
