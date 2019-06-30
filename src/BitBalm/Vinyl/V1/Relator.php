<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface Relator
{
    public function setRelationship( string $relationship_name, Relationship $relationship );
    public function getRelationship( string $source_class, string $relationship_name );
}
