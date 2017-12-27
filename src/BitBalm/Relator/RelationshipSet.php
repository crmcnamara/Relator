<?php

namespace BitBalm\Relator;


interface RelationshipSet
{
    public function addRelationship( Relationship $relationship, string $name = null ) : RelationshipSet ;
    public function getRelationship( string $fromTableName, string $relationshipName ) : Relationship ;
}
