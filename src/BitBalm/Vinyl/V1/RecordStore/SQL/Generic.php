<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL;

use BitBalm\Vinyl\V1 as Vinyl;


abstract class Generic implements Vinyl\RecordStore\SQL
{
    use Methods;
}
