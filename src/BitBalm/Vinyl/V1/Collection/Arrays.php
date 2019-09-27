<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;


use BitBalm\Vinyl\V1 as Vinyl;


class Arrays extends Vinyl\Collection
{
    public function validItem( $item ) : array
    {
        return $item;
    }
}
