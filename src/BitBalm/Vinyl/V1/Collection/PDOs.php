<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;

use PDO;


class PDOs extends Typed
{
    public function __construct() 
    {
        parent::__construct( function( PDO $item ) {}, ...func_get_args() );
    }
}
