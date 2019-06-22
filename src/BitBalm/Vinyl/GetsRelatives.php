<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl;


interface GetsRelatives
{
    public function getRelative(  
        string $relationship_name, 
        Record $source_record = null      
      ) : Record ;
      
    public function getRelatives( 
        string $relationship_name, 
        Collection\Records $source_records = null  
      ) : Collection\Records ;
}
