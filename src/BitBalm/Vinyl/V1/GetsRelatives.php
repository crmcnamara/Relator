<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface GetsRelatives
{
    public function getRelative( string $relationship_name ) : Record ;
      
    public function getRelatives( string $relationship_name ) : RecordProducer ;
}
