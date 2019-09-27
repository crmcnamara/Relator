<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;

use ArrayIterator;


/** An abstract ArrayIterator that allows its child classes 
 *    to type-check and otherwise validate items with almsot no boilerplate. 
 */
abstract class Collection extends ArrayIterator
{
    abstract protected function validItem( $item ) /* : Your type declaration here */ ;
    
    
    public function __construct( array $array = [], int $flags = 0 )
    {
        foreach ( $array as $item ) { $this->validItem($item); }
        parent::__construct( $array, $flags );
    }
    
    public function current() 
    {
        return $this->validItem( parent::current() );
    }
    
    public function append( $item )
    {
        return parent::append( $this->validItem($item) );
    }
    
    public function offsetGet( $index )
    {
        return $this->validItem( parent::offsetGet($index) );
    }
    
    public function offsetSet( $index, $item )
    {
        return parent::offsetSet( $index, $this->validItem($item) );
    }
}
