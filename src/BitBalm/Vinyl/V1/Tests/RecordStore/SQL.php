<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;


abstract class SQL extends Vinyl\Tests\RecordStore
{


    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testProducesTableName( Vinyl\RecordStore $store )
    {
        $this->assertNotEmpty(
            $store->getTable(),
            "The SQL RecordStore should produce a non-empty table name. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testProducesPrimaryKeyName( Vinyl\RecordStore $store )
    {
        $this->assertNotEmpty(
            $store->getPrimaryKey(),
            "The SQL RecordStore should produce a non-empty primary key name. "
          );
    }

    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByQueryStringThrowsRecordNotFound( Vinyl\RecordStore $store )
    {
        $exception = null;
        try {
            $store->getRecordByQueryString( 
                "SELECT * from {$store->getTable()} where {$store->getPrimaryKey()} = ? ", 
                [ 'TEST_bogus_value' ] 
              );
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The SQL RecordStore should throw an exception when it can't find matching records. "
          );
        
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByQueryStringThrowsTooManyRecords( Vinyl\RecordStore $store, $record_id )
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
            $store->getRecordByQueryString( 
                "SELECT * from {$store->getTable()} where {$field} = ? ", 
                [ $value ]
              );
        } catch ( TooManyRecords $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The SQL RecordStore should throw an exception when it finds more than one record for single-record call. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordByQueryString( Vinyl\RecordStore $store, $record_id )
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
        
        $new_record = $store->getRecordByQueryString( 
                "SELECT * from {$store->getTable()} where {$field} = ? ", 
                [ $value ]
              );
              
        $this->assertTrue(true);
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordsByQueryString( Vinyl\RecordStore $store, $record_id )
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
            
            $producer = $store->getRecordsByQueryString( 
                "SELECT * from {$store->getTable()} where {$field} = ? ", 
                [ $value ]
              );
            $records = [];
            foreach ( $producer as $key => $r ) { $records[$key] = $r; }
            
            $this->assertGreaterThan(
                1,
                count($records),
                "The SQL Recordstore should be able to get multiple records by a query string. "
              );
            
            $retrieved_ids = [];
            foreach ( $records as $record ) {
                $retrieved_ids[] = $record->getRecordId();
            }

            foreach ( $inserted_ids as $inserted_id ) {
                $this->assertContains(
                    $inserted_id,
                    $retrieved_ids,
                    "The SQL RecordStore should get all records inserted with a particular field value. "
                  );
            }
        }
    }
    
}
