<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;


abstract class GetsRelatives extends TestCase
{
    
    use TestTrait;
    
    /**
     * provides an array of scenarios, each of which consists of an array of two elements:
     *    1) A GetsRelatives implementation - the subject under test
     *    2) A relationship name by which the first argument is related to only one other record.
     */
    abstract public function getManyToOneScenarios() : array ;
 
    /**
     * provides an array of scenarios, each of which consists of an array of two elements:
     *    1) A GetsRelatives implementation - the subject under test
     *    2) A relationship name by which the first argument is related to more than one other record.
     */
    abstract public function getOneToManyScenarios() : array ;
 
    /**
     * @dataProvider getManyToOneScenarios
     */
    public function testGetsManyToOne( Vinyl\GetsRelatives $getter, string $relationship_name )
    {
        $relative = $getter->getRelative( $relationship_name );
        $this->assertTrue(true);
    }
    
    /**
     * @dataProvider getOneToManyScenarios
     */
    public function testGetsOneToMany( Vinyl\GetsRelatives $getter, string $relationship_name )
    {
        $relatives = $getter->getRelatives( $relationship_name );
        
        $relative_count = 0;
        foreach( $relatives as $r ) { $relative_count++; }
        
        $this->assertGreaterThan(
            1,
            $relative_count,
            "A GetsRelatives implementation must produce more than one relative from a one-to-many relationship. "
          );
    }
}
    
