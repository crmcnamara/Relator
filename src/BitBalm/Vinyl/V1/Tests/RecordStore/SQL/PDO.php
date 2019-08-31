<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore\SQL;

use PDO as PDOConnection;

use Atlas\Pdo\Connection;
use Atlas\Query\QueryFactory;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Collection\PDOs;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;

abstract class PDO extends Vinyl\Tests\RecordStore\SQL
{
    /**
     * returns a RecordStore instance and fixture record id
     *    for every table of every schema, using each PDO instance.
     */
    abstract public function getRecordStoreScenarios() : array ;
    

    public function getPDOs() : PDOs 
    {
        $pdos = [
            new PDO\SQLite,
            #TODO: new PDO\MySQL,
            #TODO: new PDO\PostgreSQL,
          ];
        return new PDOs($pdos);
    }
    
    public function getSchemas() : array
    {
        $schemas = [ 
            new PDO\Schema\PeopleArticles,
          ];
        return $schemas;
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testProducesPDO( Vinyl\RecordStore $store )
    {
        $this->assertNotEmpty(
            $store->getPDO(),
            "The PDO RecordStore should produce a non-empty PDO instance. "
          );
    }
    
        /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByStatementThrowsRecordNotFound( Vinyl\RecordStore $store )
    {
        $exception = null;
        try {
            $store->getRecordByStatement( 
                $store->getPDO()->prepare( "SELECT * from {$store->getTable()} where {$store->getPrimaryKey()} = ? " ), 
                [ 'TEST_bogus_value' ] 
              );
        } catch ( RecordNotFound $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The PDO RecordStore should throw an exception when it can't find matching records. "
          );
        
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */    
    public function testGetRecordByStatementThrowsTooManyRecords( Vinyl\RecordStore $store, $record_id )
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
            $store->getRecordByStatement( 
                $store->getPDO()->prepare( "SELECT * from {$store->getTable()} where {$field} = ? " ), 
                [ $value ]
              );
        } catch ( TooManyRecords $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The PDO RecordStore should throw an exception when it finds more than one record for single-record call. "
          );
    }
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordByStatement( Vinyl\RecordStore $store, $record_id )
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
        
        $new_record = $store->getRecordByStatement( 
                $store->getPDO()->prepare( "SELECT * from {$store->getTable()} where {$field} = ? " ), 
                [ $value ]
              );
        
        $this->assertTrue(true);
              
    }
    
    
    /**
     * @dataProvider getRecordStoreScenarios
     */
    public function testGetRecordsByStatement( Vinyl\RecordStore $store, $record_id )
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
            
            $records = $store->getRecordsByStatement( 
                $store->getPDO()->prepare( "SELECT * from {$store->getTable()} where {$field} = ? " ), 
                [ $value ]
              );
        
            $this->assertGreaterThan(
                1,
                count($records),
                "The PDO Recordstore should be able to get multiple records by a query string. "
              );
            
            $retrieved_ids = [];
            foreach ( $records as $record ) {
                $retrieved_ids[] = $record->getRecordId();
            }

            foreach ( $inserted_ids as $inserted_id ) {
                $this->assertContains(
                    $inserted_id,
                    $retrieved_ids,
                    "The PDO RecordStore should get all records inserted with a particular field value. "
                  );
            }
        }
    }
    
}
