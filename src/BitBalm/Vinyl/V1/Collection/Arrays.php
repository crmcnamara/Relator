<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;


class Arrays extends Typed
{
    public function __construct() 
    {
        parent::__construct( function( array $item ) {}, ...func_get_args() );
    }
}
