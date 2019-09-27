<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface Record
{
    /**
     * This method is used to hydrate records with values from the source database. 
     * The record id and all key-value pairs valid for the Record should be passed.
     * Implementors are free to require, prohibit, or ignore passing the record id 
     *    as a field-value item in the $values argument.
     * The returned record is not required to be the same record instance as the one that was called. 
     */
    public function withValues( $record_id, array $values ) : Record ;
    
    /**
     * This method should provide the record id value passed to the last call to initializeRecord(),
     * even if the id value on the Record object itself is updated in the interim.
     * This id value should be used to target the record in the source database to be updated
     *    when the Record is to be saved.
     */
    public function getRecordId();
    
    /**
     * This method should be used to retrieve valid key-value pairs 
     *    to be updated on the record in the source database
     *    when the Record is to be saved.
     * Implementations are not obligated to provide any values. 
     */
    public function getUpdatedValues() : array ;
    
    /**
     * This method should always provide all current field-value pairs that are valid for the record. 
     * Implementations may or may not provide the record id in the returned array. 
     */
    public function getAllValues() : array ;
}
