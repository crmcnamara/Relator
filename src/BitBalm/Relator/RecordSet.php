<?php

namespace BitBalm\Relator;

use ArrayAccess;

Interface RecordSet extends ArrayAccess
{
    public function asArrays() : array ;
}
