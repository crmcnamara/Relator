<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use PDO;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;


abstract class RecordStore extends TestCase
{
    abstract public function getRecordStores() : array ;
    
    /**
     * This should provide a record id of a pre-existing record 
     *    in each RecordStore provided by getRecordStores().
     */
    abstract public function getFixtureRecordIds() : array ;
    
    
    use TestTrait;
    
    
    public function getRecordStoreScenarios()
    {
        // cache the result of the first call to this method
        static $scenarios;
        if ( !empty($scenarios) ) { return $scenarios; }
        
        $record_ids = array_values( $this->getFixtureRecordIds() ); 
          
        foreach ( $this->getRecordStores()  as $storename => $store ) {
            $record_id = array_shift( $record_ids );
            $scenarios["store {$storename}"] = [ $store, $record_id ]; 
        }
        
        return $scenarios;
    }
    

    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordThrowsNotFound( Vinyl\RecordStore $store )
    {
        foreach( [ 'TEST_bogus_record_id_9999', 999999, ] as $record_id ) {
          
            $exception = null;
            try {
                
                $record = $store->getRecord( $record_id );
            } catch ( RecordNotFound $exception ) {}
        
            verify(
                "Throws RecordNotFound when a missing record is requested. ",
                $exception
              )->notEmpty();
        }
    }
        
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecord( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        verify(
            "Gets a record by id that matches the id from the resulting Record. ",
            $record->getRecordId()
          )->Equals($record_id);
    }

    
    protected function getIdFields( Vinyl\Record $record ) : array
    {
        // operate on a clone of the passed record
        $record = clone $record;
        
        // mutate the record id of the clone
        $record_id = $record->getRecordId();
        $new_record_id = is_numeric($record_id) ? $record_id +99999 : $record_id .'_TEST';
        
        $record->initializeRecord( $new_record_id, $record->getAllValues() );
        
        // record any fields whose values match the mutated id
        $fields = [];
        foreach ( $record->getAllValues() as $field => $value ) {
            if ( $value == $new_record_id ) { $fields[] = $field; }
        }
        
        return $fields;
    }
    
    protected function getValuesWithoutId( Vinyl\Record $record ) : array
    {
        $id_fields = $this->getIdFields($record);
        
        $values = [];
        foreach ( $record->getAllValues() as $field => $value ) {
            if ( ! in_array( $field, $id_fields, true ) ) { $values[$field] = $value; }
        }
        
        return $values;
    }
    
    public function getRecordsScenarios()
    {
        $scenarios = [];
        $record_stores = $this->getRecordStores();
        $fixture_record_ids = array_values( $this->getFixtureRecordIds() ); 
        
        foreach ( $this->getRecordStores() as $storename => $store ) {
            $fixture_record_id = array_shift( $fixture_record_ids );
            foreach ( [ 
                'all records' => [ $fixture_record_id ], 
                'some records' => [ $fixture_record_id, 'TEST_bogus_record_id_9999', 999999, ],
                'only missing records' => [ 'TEST_bogus_record_id_9999', 999999, ],
                'no records' => [], 
              ] as $subtitle => $requested_record_ids )
            {
                $scenarios[ "{$storename} - {$subtitle}" ] = 
                    [ $store, $fixture_record_id, $requested_record_ids, ];
            }
        }
        return $scenarios;
    }
    
    /** 
     * @dataProvider getRecordsScenarios
     */
    public function testGetRecords( Vinyl\RecordStore $store, $fixture_record_id, array $record_ids )
    {
        $expected_records = [];
        
        try {
            // attempt to fetch the first record id as a fixture record
            $record = $store->getRecord( current( $record_ids ) );
            $expected_records[] = $record;
            
            // and, if found, insert a second for good measure
            $insert_values = $this->mutateValues( $this->getValuesWithoutId($record) );
            $inserted_record = $store->insertRecord($insert_values);
            $record_ids[] = $inserted_record->getRecordId();
            $expected_records[] = $inserted_record;
            
        } catch ( RecordNotFound $e ) {}
        
        $expected_record_count = count($expected_records);
        
        
        $records = $store->getRecords( $record_ids );
        
        
        verify(
            "The RecordStore provides {$expected_record_count} results. ",
            count($records)
          )->Equals($expected_record_count);
          
        foreach( $expected_records as $expected_record ) {
            $found = 0;
            foreach ( $records as $record ) {
                if ( $record->getRecordId() == $expected_record->getRecordId() ) { 
                    $found++; 
                    break; 
                }
            }
            verify(
                "The RecordStore provides a record for id {$expected_record->getRecordId()}. ",
                $found
              )->Equals(1);
        }
        
    }


    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testInsertRecord( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord($record_id);
        
        $insert_values = $this->mutateValues( $this->getValuesWithoutId($record) );
        $inserted_record = $store->insertRecord( $insert_values );
        
        verify(
            "Inserting a new record yields a Record with a new, non-empty id. ",
            $inserted_record->getRecordId()
          )->notEmpty()->notEquals( $record->getRecord_id() );
        
        $inserted_values = $record->getAllValues();        
        
        foreach ( $insert_values as $field => $value ) {
            verify(
                "Inserting a new record yields a Record with all the inserted values. ",
                $inserted_values[$field]
              )->Equals($insert_values[$field]);
        
    }
    
    
}
