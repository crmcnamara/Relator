<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;

use BitBalm\Vinyl\V1\Record;


class Records extends Typed
{
    public function __construct() 
    {
        parent::__construct( function( Record $item ) {}, ...func_get_args() );
    }
}
