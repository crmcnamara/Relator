<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use InvalidArgumentException;
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


    protected function getIdFields( Vinyl\RecordStore $store, $record_id ) : array
    {
        // fetch the fixture record directly from the store
        $record = $store->getRecord( $record_id );
        $values = $record->getAllValues();
        
        // Identify fields whose values match that match the current record id
        // Some or all of these values may match purely coincidentally,
        //    rather than because they're the id field.
        $id_fields = array_keys( $values, $record->getRecordId() );
        
        // mutate the value of only those fields, 
        $insert_values = array_merge( 
            $values, 
             array_intersect_key( $this->mutateValues($record), array_flip($id_fields) )
          );
          
        // and insert a new record with those values
        $record = $store->insertRecord( $insert_values );
        
        // now check again - any fields whose values matched purely coincidentally should be eliminated.
        $id_fields = array_keys( $record->getAllValues(), $record->getRecordId() );
        
        // cleanup
        $store->deleteRecord( $record );
        
        return $id_fields;
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
                    [ $store, $requested_record_ids, ];
            }
        }
        return $scenarios;
    }
    
    /** 
     * @dataProvider getRecordsScenarios
     */
    public function testGetRecords( Vinyl\RecordStore $store, array $record_ids )
    {
        $expected_records = [];
        
        try {
            // attempt to fetch the first record id as a fixture record
            $record_id = current( $record_ids );
            $record = $store->getRecord( $record_id );
            $expected_records[] = $record;
            
            // and, if found, insert a second for good measure
            // strip field-value pairs containing ids, if any
            $insert_values = array_diff_key(
                $record->getAllValues(), 
                array_flip( $this->getIdFields( $store, $record_id ) )
              );
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
        
        // strip field-value pairs containing ids, if any
        $insert_values = array_diff_key(            
            $this->mutateValues($record),
            array_flip( $this->getIdFields( $store, $record_id ) )
          );
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
