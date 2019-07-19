<?php
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Collection;

use ArrayObject;


/** an ArrayObject that allows validation and typing-checking of its items. 
 */
class Typed extends ArrayObject
{
    protected $validator;
    
    
    /** 
     * @parameter callable $validator - A closure that takes a single Collection item as an argument. 
     * To type-hint the Collection's items, just pass: function( SomeClass $item ) {}
     * For more complex validation, put it in the function code and throw exceptions to fail. 
     */
    public function __construct( 
        callable $validator, 
        $input = [], 
        $flags = 0, 
        $iterator_class = "ArrayIterator" 
      )
    {
        $this->validator = $validator;
        #TODO: bind $validator closure to $this
        $this->validateItems($input);
        parent::__construct( $input, $flags, $iterator_class);
    }
    
    /** Validates each item passed in an array against the validator passed in the constructor.
     */
    public function validateItems( array $items ) 
    {
        foreach ( $items as $item ) { ($this->validator)($item); }
    }
    
    public function exchangeArray($input) : array
    {
        $this->validateItems($input);
        return parent::exchangeArray($input);
    }
    
    public function offsetSet( $index, $newval )
    {
        $this->validateItems([$newval]);
        return parent::offsetSet( $index, $newval );
    }
    
    public function append($value)
    {
        $this->validateItems([$value]);
        return parent::append($value);
    }
    
}
