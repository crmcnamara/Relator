<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;

use BitBalm\Vinyl\V1 as Vinyl;


class GetsRelatives extends Generic implements Vinyl\GetsRelatives
{
    use GetsRelatives\Implementation;
    
    protected /* Vinyl\Relator */ $relator;
    
    public function __construct( 
        Vinyl\Relator $relator, 
        $input = [], 
        int $flags = 0, 
        string $iterator_class = "Arrayiterator" 
      )
    {
        $this->relator = $relator;
        parent::__construct( $input, $flags, $iterator_class );
    }
    
    protected function getRelator() : Vinyl\Relator
    {
        return $this->relator;
    }
    
    protected function getSourceRecord() : Vinyl\Record
    {
        return $this;
    }
}
