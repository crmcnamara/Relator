<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl;


interface Record
{
    public function setValues( array $values );
    public function getValues() : array ;    
    
    public function setStoreId( $store_id );
    public function getStoreId();
}
