<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Relationship\Simple;

use BitBalm\Vinyl\V1 as Vinyl;


trait Implementation /* implements Vinyl\Relationship */
{
    protected /* string */ $source_class;
    protected $source_field;
    protected /* Viny\RecordStore */ $destination_store;
    protected $detination_field;
    
    
    public function __construct( 
        string $source_class, 
        $source_field, 
        Vinyl\RecordStore $destination_store, 
        $destination_field 
      )
    {
        $this->source_class = $source_class;
        $this->source_field = $source_field;
        $this->destination_store = $destination_store;
        $this->destination_field = $destination_field;
    }
    
    public function sourceClass() : string 
    {
        return $this->source_class;
    }
    
    public function sourceField()
    {
        return $this->source_field;
    }
    
    public function destinationRecordStore() : Vinyl\RecordStore 
    {
        return $this->destination_store;
    }
    
    public function destinationField()
    {
        return $this->destination_field;
    }
}
