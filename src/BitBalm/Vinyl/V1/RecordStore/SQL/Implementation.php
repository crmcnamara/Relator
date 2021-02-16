<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;
use BitBalm\Vinyl\V1\Exception\InvalidField;


trait Implementation /* implements Vinyl\RecordStore\SQL */
{
    protected $table_name;
    protected $primary_key_name;
    protected $field_names;
    
    public function __construct( 
        string $table_name, 
        string $primary_key_name,
        array  $field_names = []
      )
    {
        $this->table_name       = $table_name;
        $this->primary_key_name = $primary_key_name;
        $this->field_names      = $field_names;
    }
    
    public function getTable()
    {
        return $this->table_name;
    }

    public function getPrimaryKey()
    {
        return $this->primary_key_name;
    }
    
    protected function validField( string $field ) : string
    {
        $valid_field = array_combine( $this->field_names, $this->field_names )[$field] ?? null;
        
        if ( empty($valid_field) ) {
            throw new InvalidField("Field {$field} is not valid for table {$this->getTable()} . ");
        }
        
        return $field;
    }

    
    public function getRecordByQueryString(  string $query, array $parameters ) : Record 
    {
        $records = $this->getRecordsByQueryString( $query, $parameters );
        return $this->getOnlyRecord($records);
    }
    

    protected function getOnlyRecord( Vinyl\RecordProducer $records )
    {
        $records->rewind();
        
        if ( ! $records->valid() )  { 
            throw new RecordNotFound( "No {$this->getTable()} record was found. " ); 
        }
        
        $record = $records->current();
        $records->next();
        if ( $records->valid() ) { 
            throw new TooManyRecords( "Multiple {$this->getTable()} records were found. " ); 
        }
        
        return $record;
    }

}
