<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;


trait TestTrait
{
    /**
     * Alters the values from a record, insuring each value is:
     *    + different from all other values in the returned Collection, and
     *    + different from the original value.
     * Currently supports numeric and string values.
     * Implementors may need to override this to support their own value types. 
     */
    protected function mutateValues( Vinyl\Record $record, int $seed = 1 ) : array #Vinyl\Collection\Uniques
    {
        $values = $record->getAllValues();
        $mutated = [];
        foreach( $values as $key => $value ) {
            $seed++;
            $mutated[$key] = 
                is_numeric( $value ) 
                    ? $value + intval( str_repeat( (string) $seed, 4 ) )
                    : $values[$key] . $seed;
        }
        return $mutated;
    }
    
    protected function abbreviateClass( /*object*/ $object, $count = 2 )
    {
        $bits = explode( '\\', get_class($object) );
        $short_bits = [];
        while ( $count-- >0 ) {
            array_unshift( $short_bits, array_pop( $bits ) );
        }
        return implode( '\\', $short_bits );
    }

}
