<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\Record;


use BitBalm\Vinyl\V1 as Vinyl;


class Generic extends Vinyl\Tests\Record
{
    
    public function getRecords() : array 
    {
      
        $record = (new Vinyl\Record\Generic)
            ->withValues( 9, [ 'id' => 9, 'name' => 'Kelly' ] );

        return [ $record ];
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testWithValuesSameId( Vinyl\Record $record )
    {
        $altered_values = $this->mutateValues($record);
        
        $new_record = $record->withValues( $record->getRecordId(), $altered_values );
        
        $this->assertSame(
            $record,
            $new_record,
            "Calling withValues() with the same record id as the called Record "
                ."should return the same Record instance. "
          );
          
        $this->assertEquals(
            $altered_values,
            $record->getAllValues(),
            "Calling withValues() with the same record id as the called Record "
                ."should persist the passed values. "
          );
        
    }
    
    /**
     * @dataProvider RecordScenarios
     */
    public function testWithValuesMoveId( Vinyl\Record $record )
    {
        $record_id = $record->getRecordId();
        $altered_values = $this->mutateValues($record);
        
        $new_record_id = 999999;
        
        $moved_record = $record->withValues( $new_record_id, $altered_values );
        
        $this->assertNotSame(
            $record,
            $moved_record,
            "Calling withValues() with a changed record id on a record with a record id, "
                ."should return a different Record instance than the called Record. "
          );
          
          
        $this->assertEquals(
            $new_record_id,
            $record->getRecordId(),
            "After calling withValues() with a changed record id on a record with a record id, "
                ."the called Record should have the changed id. "
          );
          
        $this->assertEquals(
            $altered_values,
            $record->getAllValues(),
            "After calling withValues() with a changed record id on a record with a record id, "
                ."the called Record should persist the passed values. "
          );
        
        
        $this->assertEquals(
            $new_record_id,
            $moved_record->getRecordId(),
            "Calling withValues() with a changed record id on a record with a record id "
                ."should return a Record with the changed id. "
          );    

        $this->assertEquals(
            $altered_values,
            $record->getAllValues(),
            "Calling withValues() with a changed record id on a record with a record id "
                ."should return a Record that persists the passed values. "
          );
        
        $further_altered_values = $this->mutateValues($moved_record);
        $mutated_record = $moved_record->withValues( $moved_record->getRecordId(), $further_altered_values );
        
        $this->assertSame(
            $moved_record,
            $mutated_record,
            "Calling withValues() with the same record id as the called Record "
                ."should return the called Record instance. "
          );
        
        $this->assertNotSame(
            $record->getAllValues(),
            $mutated_record->getAllValues(),
            "Record values on different instances should be stored independently. "
          );
        
    }
    

    
    /**
     * @dataProvider RecordScenarios
     */
    public function testWithValuesFromPrototype( Vinyl\Record $record )
    {
        $prototype_values = $this->mutateValues($record);
        $prototype = $record->withValues( null, $prototype_values );
        
        $new_values = $this->mutateValues($prototype);
        $new_record_id = 999999;
        $new_record = $prototype->withValues( $new_record_id, $new_values );
        
        $this->assertNotSame(
            $prototype,
            $new_record,
            "Calling withValues() with a new record id on a Record which lacks a record id "
                ."should return a separate Record instance from that which was called. "
          );
        
        
        $this->assertSame(
            null,
            $prototype->getRecordId(),
            "After calling withValues() with a new record id on a Record which lacks a record id, "
                ."the called Record should still lack a record id. "
          );
          
        $this->assertEquals(
            $prototype_values,
            $prototype->getAllValues(),
            "After calling withValues() with a new record id on a Record which lacks a record id, "
                ."the called Record should retain its original values. "
          );
        
        
        $this->assertSame(
            $new_record_id,
            $new_record->getRecordId(),
            "Calling withValues() with a new record id on a Record which lacks a record id "
                ."Should return a Record with the passed id. "
          );
        
        $this->assertEquals(
            $new_values,
            $new_record->getAllValues(),
            "Calling withValues() with a new record id on a Record which lacks a record id "
                ."Should return a Record with the passed values. "
          );
        
    }
      
    /**
     * @dataProvider RecordScenarios
     */
    public function testWithValuesToPrototype( Vinyl\Record $record )
    {
        $record_id = $record->getRecordId();
        $record_values = $record->getAllValues();
        $altered_values = $this->mutateValues($record);
        $prototype = $record->withValues( null, $altered_values );
        
        $this->assertNotSame(
            $record,
            $prototype,
            "Calling withValues() with a null record id "
                ."should return a separate Record instance from that which was called. "
          );
        
          
        $this->assertSame(
            $record_id,
            $record->getRecordId(),
            "After calling withValues() with a null record id, "
                ."the called Record should return its original record id. "
          );
        
        $this->assertEquals(
            $record_values,
            $record->getAllValues(),
            "After calling withValues() with a null record id, "
                ."the called record should retain its original values. "
          );
        
          
        $this->assertSame(
            null,
            $prototype->getRecordId(),
            "Calling withValues() with a null record id "
                ."should return a Record which returns null for getRecordId(). "
          );
          
        $this->assertEquals(
            $altered_values,
            $prototype->getAllValues(),
            "Calling withValues() with a null record id "
                ."should return a Record that persists the passed values. "
          );
        
        
    }
    
}
