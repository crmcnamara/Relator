<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;


abstract class Record extends TestCase 
{
    abstract public function getRecords() : array ;
    
    
    use TestTrait;
    
    
    public function RecordScenarios()
    {
        // cache the result of the first call to this method
        static $scenarios;
        if ( !empty($scenarios) ) { return $scenarios; }
        
        foreach ( $this->getRecords() as $record ) { 
                $scenarios[] = [ $record ]; 
        }
        
        return $scenarios;
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testGetAllValues( Vinyl\Record $record )
    {
        $initial_values = $record->getAllValues();
        
        $null_count = 0;
        foreach( $initial_values as $key => $value ) { if ( is_null($value) ) { $null_count++; } }
            
        $this->assertLessThan(
            count($initial_values),
            $null_count,
            "The Record's getAllValues() call must provide at least some non-null values. "            
          );
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testInitializeRecord( Vinyl\Record $record )
    {        
        $altered_values = $this->mutateValues($record);
        
        $new_record_id = 999999;
        
        
        $record->initializeRecord( $new_record_id, $altered_values );
        
        
        $this->assertEquals( 
            $new_record_id,
            $record->getRecordId(),
            "The Record must persist the record id passed to a call to initializeRecord(). "
          );
        
        ksort($altered_values);
        $persisted_values = $record->getAllValues();
        ksort($persisted_values);
        
        $this->assertEquals( 
            $altered_values,
            $persisted_values,
            "The Record must persist values passed to a call to initializeRecord(). "
          );
        
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testGetRecordId( Vinyl\Record $record )
    {
        $initial_record_id = $record->getRecordId();
        
        $this->assertTrue(
            !empty($initial_record_id),
            "The Record should return a non-empty value for getRecordId(). "
          );
        
    }
    
}
