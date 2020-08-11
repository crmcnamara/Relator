<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection\Records;

use BitBalm\Vinyl\V1 as Vinyl;


class GetsRelatives extends Vinyl\Collection\Records implements Vinyl\GetsRelatives
{
    use GetsRelatives\Implementation;
    
    
    protected /* Vinyl\Relator */ $relator;
    
    
    public function __construct( Vinyl\Relator $relator, array $items = [], int $flags = 0 )
    {
        $this->relator = $relator;
        parent::__construct( $items, $flags );
    }
    
    protected function getRelator() : Vinyl\Relator
    {
        return $this->relator;
    }
}
