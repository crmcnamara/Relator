<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\Collection;


use InvalidArgumentException;
use TypeError;
use ArgumentCountError;

use stdClass;

use PHPUnit\Framework\TestCase;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Collection\Arrays;
use BitBalm\Vinyl\V1\Collection\Records;
use BitBalm\Vinyl\V1\Collection\PDOs;


class Typed extends TestCase
{
    public function getScenarios()
    {
        $scenarios = [
        
            'integer collection' => [ 
            
                // the collection subject under test
                'collection'  => new Vinyl\Collection\Typed( 
                    function( int $item ) {} 
                  ),
                
                // an example item the collection will accept.
                'accepted'    => 99,
                
              ],
              
            Arrays::class   => [ new Arrays,  [1], ],
            Records::class  => [ new Records, new Vinyl\Record\Generic, ],
            PDOs::class     => [ new PDOs,    new Vinyl\Tests\RecordStore\SQL\PDO\SQLite, ],
            
          ];    
        
        return $scenarios;
    }
       
    
    /**
     * child class tests
     * All classes that inherit from Vinyl\Collection\Typed should pass these.
     * To test them, extend this test case and override getScenarios(), 
     *    adding your own scenarios for new subclasses. 
     */
       
       
    /**
     * @dataProvider getScenarios
     */
    public function testRejectsItemsByArrayAccess( Vinyl\Collection\Typed $collection )
    {
        // We can't really be sure what the Collection is looking for,
        //    but we do know it probably can't type-hint for two things at the same time. 
        // So we attempt to assign multiple distinct types,
        //    under the assumption that one or another will not fit. 
        $bogus_items = [ 'bogus test string', 9, new stdClass, null ];
        
        
        $exception = null;
        try {
            foreach( $bogus_items as $item ) {
                $collection['bogus_test_key'] = $item; 
            }
        } catch ( InvalidArgumentException $e ) {
        } catch ( TypeError $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The typed collection should throw an InvalidArgumentException or TypeError "
                ."when a user attempts to set an invalid item. "
          );
        
        
        // Same as above, but for appending
        $exception = null;
        try {
            foreach( $bogus_items as $item ) {
                $collection[] = $item;
            }
        } catch ( InvalidArgumentException $exception ) {
        } catch ( TypeError $exception ) {}
        
        $this->assertNotEmpty(
            $exception,
            "The typed collection should throw an InvalidArgumentException or TypeError "
                ."when a user attempts to set an invalid item. "
          ); 
            
    }
    
    /**
     * @dataProvider getScenarios
     */
    public function testAcceptsItemsByArrayAccess( Vinyl\Collection\Typed $collection, $accepted )
    {
        $collection['bogus_test_key'] = $accepted;
        $collection[] = $accepted;
        $this->assertTrue(true);
    }

    
    /** 
     * Collection\Typed implementation tests
     * These methods test implemenation details specific to the Collection\Typed class.
     * Subclasses of it may have a completely different constructor signature.
     */

    public function testRejectsInitialItems()
    {
        try {
            new Vinyl\Collection\Typed( function( int $item ) {}, [ 9, 'bogus test string', ] );
        } catch ( InvalidArgumentException $exception ) {
        } catch ( TypeError $exception ) {}
        
        $this->assertTrue(
            !empty($exception),
            "A typed collection should throw an InvalidArgumentException or TypeError "
                ."when passed invalid items as constructor arguments. "
          );
    }
    
    public function testRejectsEmptyValidator()
    {
        try {
            new Vinyl\Collection\Typed();
        } catch ( ArgumentCountError $error ) {}
        
        $this->assertTrue(
            !empty($error),
            "A typed collection should throw an ArgumentCountError "
                ."when instantiated without a validator callable. "
          );
    }
    
    public function testAcceptsInitialEmptiness()
    {
        new Vinyl\Collection\Typed( function( int $item ) {}, [] );
        new Vinyl\Collection\Typed( function( int $item ) {} );
        $this->assertTrue(true);
    }
    
    public function testAcceptsInitialItems()
    {
        $items = [ 9, 12, 99999999, ];
        $collection = new Vinyl\Collection\Typed( function( int $item ) {}, $items );
        
        $this->assertEquals( 
            (array) $collection,
            $items,
            "A typed collection should accept items provided as constructor arguments. "
          );
    }
        
    public function testBindsClosures()
    {
        $string = 'test string';
        
        $validator = function( int $item ) { $this->test_prop = 'test string'; };
        
        $collection = new Vinyl\Collection\Typed( $validator, [ 9 ] );
        
        $this->assertTrue(
            !empty($collection->test_prop),
            "A typed array should bind its validator closure to itself. "
          );
    }
    
    
}
