<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;


abstract class Record extends TestCase 
{
    abstract public function getRecords() : array ;
    
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
            
        verify(
            "The Record's getAllValues() call must provide at least some non-null values. ",
            $null_count
          )->lessThan(count($initial_values));
    }
    
    protected function alterValues( array $values ) : array 
    {   
        $number = 1;
        foreach( $values as $key => $value ) {
            $number++;
            $altered_values[$key] = 
                is_numeric( $value ) 
                    ? $value + $number
                    : $values[$key] . $number;
        }
        return $altered_values;
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testInitializeValues( Vinyl\Record $record )
    {
        // to determine what fields are valid for this record, we ask it
        $initial_values = $record->getAllValues();
        
        $altered_values = $this->alterValues( $initial_values );
        $record->initializeValues( $altered_values );
        
        verify( 
            "The Record must persist values passed to a call to initializeValues(). ", 
            $record->getAllValues()
          )->equals($altered_values);
        
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testGetRecordId( Vinyl\Record $record )
    {
        $initial_record_id = $record->getRecordId();
        $initial_values = $record->getAllValues();
        
        verify(
            "The Record should return a non-empty value for getRecordId(). ",
            $initial_record_id
          )->notEmpty();
          
        // to determine the id field for this record, 
        //    we look for the value returned by getRecordId() in getAllValues()
        $id_field = array_search( $initial_record_id, $initial_values, true );
        
        verify( 
            "The initial call to getAllValues() should include the value returned by GetRecordId(). ",
            $initial_values
          )->contains($initial_record_id);
            
        $altered_values = $this->alterValues( $record->getAllValues() );
        $record->initializeValues( $altered_values );
        
        verify(
            "After a call to initializeValues(), getRecordId() should return the id value passed to the first call. ",
            $record->getAllValues()[$id_field]
          )->Equals( $altered_values[$id_field] );
        
    }
    
}
