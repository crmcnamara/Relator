<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use InvalidArgumentException;
use PDO;


use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;
use BitBalm\Vinyl\V1\Exception\InvalidField;


abstract class RecordStore extends TestCase
{
    
    use TestTrait;
    
    /**
     * provides an array of scenarios, each of which consists of an array of two elements:
     *    1) A RecordStore implementation - the subject under test
     *    2) A Record id of a record that already exists in the RecordStore
     */
    abstract public function getRecordStoreScenarios();
    
    
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
        
            $this->assertNotEmpty(
                $exception,
                "Throws RecordNotFound when a missing record is requested. "
              );
        }
    }
        
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecord( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        $this->assertEquals(
            $record_id,
            $record->getRecordId(),
            "Gets a record by id that matches the id from the resulting Record. "
          );
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
        
        foreach ( $this->getRecordStoreScenarios() as $storename => $scenario ) {
            $store = current( $scenario );
            $fixture_record_id = end( $scenario );
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
        
        
        $this->assertEquals(
            $expected_record_count,
            count($records),
            "The RecordStore provides {$expected_record_count} results. "
          );
          
        foreach( $expected_records as $expected_record ) {
            $found = 0;
            foreach ( $records as $record ) {
                if ( $record->getRecordId() == $expected_record->getRecordId() ) { 
                    $found++; 
                    break; 
                }
            }
            $this->assertEquals(
                1,
                $found,
                "The RecordStore provides a record for id {$expected_record->getRecordId()}. "
              );
        }
        
    }

    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordByFieldValueThrowsInvalidField( Vinyl\RecordStore $store )
    {
        $exception = null; 
        try {
            $store->getRecordByFieldValue( 'TEST_bogus_fieldname', 1 );
        } catch ( InvalidField $exception ) {}
        
        $class = InvalidField::class;
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw {$class} when passed an invalid fieldname. "
          );
    }

    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByFieldValueThrowsRecordNotFound( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id ) ;
        $field = current( array_keys( $record->getAllValues() ) );
        
        $exception = null;
        try {
            $store->getRecordByFieldValue( $field, 'TEST_bogus_value' );
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw an exception when it can't find matching records. "
          );
        
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByFieldValueThrowsTooManyRecords( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id ) ;
        
        // strip field-value pairs containing ids, if any
        $insert_values = array_diff_key(
            $this->mutateValues($record),
            array_flip( $this->getIdFields( $store, $record_id ) )
          );
        $store->insertRecord($insert_values);
        $store->insertRecord($insert_values);
        
        $field = current( array_keys( $insert_values ) );
        $value = $insert_values[$field];
        
        $exception = null;
        try {
            $store->getRecordByFieldValue( $field, $value );
        } catch ( TooManyRecords $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw an exception when it finds more than one Record for single-record call. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordByFieldValue( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        // strip field-value pairs containing ids, if any
        $insert_values = array_diff_key(
            $this->mutateValues($record),
            array_flip( $this->getIdFields( $store, $record_id ) )
          );
        $store->insertRecord($insert_values);
        
        $field = current( array_keys( $insert_values ) );
        $value = $insert_values[$field];
        
        $new_record = $store->getRecordByFieldValue( $field, $value );
        
        $this->assertTrue(true);
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordsByFieldValuesThrowsInvalidField( Vinyl\RecordStore $store )
    {
        $exception = null; 
        try {
            $records = $store->getRecordsByFieldValues( 'TEST_bogus_fieldname', [ 1 ] );
        } catch ( InvalidField $exception ) {}

        $class = InvalidField::class;
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw {$class} when passed an invalid fieldname. "
          );
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testInsertRecordThrowsInvalidField( Vinyl\RecordStore $store )
    {
        try {
            $store->insertRecord([ 'TEST_bogus_fieldname' => 1 ]);
        } catch ( InvalidField $exception ) {}

        $class = InvalidField::class;
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw {$class} when passed an invalid fieldname. "
          );
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordsByFieldValues( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        $id_fields = $this->getIdFields( $store, $record_id );
        
        // strip field-value pairs containing ids, if any
        $insert_values = array_diff_key(
            $this->mutateValues($record),
            array_flip( $id_fields )
          );
        $inserted_ids[] = $store->insertRecord($insert_values)->getRecordId();
        $inserted_ids[] = $store->insertRecord($insert_values)->getRecordId();
        
        foreach ( $insert_values as $field => $value ) {
            
            $records = $store->getRecordsByFieldValues( $field, [ $value ] );
        
            $this->assertGreaterthan(
                1,
                count($records),
                "The Recordstore should be able to get multiple records by a field's value. "
              );
            
            $retrieved_ids = [];
            foreach ( $records as $record ) {
                $retrieved_ids[] = $record->getRecordId();
            }

            foreach ( $inserted_ids as $inserted_id ) {
                $this->assertContains(
                    $inserted_id,
                    $retrieved_ids,
                    "The RecordStore should get all records inserted with a particular field value. "
                  );
            }
        }
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordsByFieldValuesAcceptsEmptyValues( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        foreach ( array_keys( $record->getAllValues() ) as $field ) {
            $records = $store->getRecordsByFieldValues( $field, [] );
            
            $this->assertEquals(
                count($records),
                0,
                "The RecordStore should return an empty Record collection when provided no values for any field. "
              );
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
        
        $this->assertNotEmpty(
            $inserted_record->getRecordId(),
            "Inserting a new record yields a Record with a new, non-empty id. "
          );
          
        $this->assertNotEquals(
            $record->getRecordId(),
            $inserted_record->getRecordId(),
            "Inserting a new record yields a Record with a new, non-empty id. "
          );
          
        $inserted_values = $inserted_record->getAllValues();
        
        foreach ( $insert_values as $field => $value ) {
            $this->assertEquals(
                $insert_values[$field],
                $inserted_values[$field],
                "Inserting a new record yields a Record with all the inserted values. "
              );
        }

        $second_inserted_record = $store->insertRecord( $insert_values );
        
        $this->assertNotEquals(
            $inserted_record->getRecordId(),
            $second_inserted_record->getRecordId(),
            "A second record insertion with the same values should yield a record with a different id. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testUpdateRecordThrowsNotFound( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        $exception = null;
        
        
        // strip the id fields from values of the fixture record
        $update_values = array_diff_key(
            $record->getAllValues(), 
            array_flip( $this->getIdFields( $store, $record_id ) )
          );
        // re-initialize the record with a bogus id
        $record->initializeRecord( 999999, $update_values );

        try {
            $store->updateRecord($record);
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw an exception when it can't find a record to update. "
          );
          
        
    }

    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testUpdateMovingRecordThrowsNotFound( Vinyl\RecordStore $store, $record_id )
    {
        $exception = null;
        
        $id_fields = $this->getIdFields( $store, $record_id );
        if ( ! empty(count($id_fields)) ) {
            
            $record = $store->getRecord( $record_id );
            
            // re-initialize the record with a bogus id for a non-existent record,
            //    but keep the original id as an update value
            $record->initializeRecord( 999999, $record->getUpdatedValues() );

            try {
                $store->updateRecord($record);
            } catch ( RecordNotFound $exception ) {}
            
            $this->assertNotEmpty(
                $exception,
                "The RecordStore should throw an exception when it can't find a record to update. "
              );

        }
        
    }

    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testUpdateRecord( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        $updated_record = $store->updateRecord($record);
        
        $this->assertSame(
            $record,
            $updated_record,
            "The RecordStore should return the same record object instance when updating a record. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testUpdateMovesRecord( Vinyl\RecordStore $store, $record_id )
    {
        $target_id = 999999;
      
        $id_fields = $this->getIdFields( $store, $record_id );
        foreach ( $id_fields as $id_field ) {
          
            $record = $store->getRecord( $record_id );
            $values = $record->getAllValues();
            $values[$id_field] = $target_id;
            $record->initializeRecord( $record_id, $values );
            
            $store->updateRecord($record);
            
            $this->assertEquals(
                $target_id,
                $record->getRecordId(),
                "A RecordStore that allows updates to id fields should move them properly. "
              );
              
            $this->assertEquals(
                $target_id,
                $record->getAllValues()[$id_field],
                "A RecordStore that allows updates to id fields should move them properly. "
              );
              
            $exception = null;
            
            try {
                $store->getRecord($record_id);
            } catch ( RecordNotFound $exception ) {}
            
            $this->assertNotEmpty(
                $exception,
                "A RecordStore that allows moving records by updating id fields "
                    ."should not be able to retrieve the original record after moving it. "
              );
        }
        
        $this->assertTrue(true);
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testDeleteRecordThrowsNotFound( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        $exception = null;
        
        // re-initialize the record with a  bogus id
        $record->initializeRecord( 999999, $record->getAllValues() );
        
        try {
            $store->deleteRecord($record);
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should throw an exception when it can't find a record to delete. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testDeleteRecord( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord( $record_id );
        
        $store->deleteRecord($record);
        
        try {
            $store->getRecord( $record_id );
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The RecordStore should delete records. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testWithRecordGet( Vinyl\RecordStore $store, $record_id )
    {
        $prototype_record = new class extends Vinyl\Record\Generic {};
        $new_store = $store->withRecord($prototype_record);
        
        $record = $new_store->getRecord($record_id);
        
        $this->assertInstanceOf(
            get_class($prototype_record),
            $record,
            "A record retrieved from a RecordStore returned from RecordStore::withRecord() "
                ."should be the same class as the record passed to withRecord() . "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testWithRecordInsert( Vinyl\RecordStore $store, $record_id )
    {
        $record = $store->getRecord($record_id);
        
        // strip field-value pairs containing ids, if any
        $insert_values = array_diff_key(
            $this->mutateValues($record),
            array_flip( $this->getIdFields( $store, $record_id ) )
          );
          
        $prototype_record = new class extends Vinyl\Record\Generic {};
        $new_store = $store->withRecord($prototype_record);
        
        
        $new_record = $new_store->insertRecord($insert_values);
        
        
        $this->assertInstanceOf(
            get_class($prototype_record),
            $new_record,
            "A record inserted to a RecordStore returned from RecordStore::withRecord() "
                ."should be the same class as the record passed to withRecord() . "
          );
    }
    
}
