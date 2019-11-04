<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface Relator
{
    public function setRelationship( string $relationship_name, Relationship $relationship );
    public function getRelationship( Record $source_record, string $relationship_name ) : Relationship ;
}
