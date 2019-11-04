<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Relator\Simple;

use BitBalm\Vinyl\V1 as Vinyl;


trait Implementation /* implements Vinyl\Relator */
{
    protected /* array */ $relationships = [];
    
    
    public function setRelationship( string $relationship_name, Vinyl\Relationship $relationship )
    {
        $this->relationships[$relationship_name][] = $relationship;
        return $this;
    }
    
    public function getRelationship( Vinyl\Record $source_record , string $relationship_name ) : Vinyl\Relationship
    {
        $relationships = $this->relationships[$relationship_name] ?? [];
        foreach ( $relationships as $relationship ) {
            $source_class = $relationship->sourceClass();
            if ( $source_record instanceof $source_class ) {
                return $relationship;
            }
        }

        throw new Vinyl\Exception\RelationshipNotFound( "No Relationships were found for: {$relationship_name}. " );
    }
    
}
