<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\Collection;

use InvalidArgumentException;

use PHPUnit\Framework\TestCase;
use BitBalm\Vinyl\V1 as Vinyl;


class Typed extends TestCase
{
    public function getScenarios()
    {
        $scenarios = [
        
            'RecordCollection' => [ 
            
                // the collection subject under test
                'collection'  => new Vinyl\Collection\Typed( 
                    function( Vinyl\Record $record ) {} 
                  ),
                
                // an example item the collection will accept.
                'accepted'    => new Vinyl\Record\Generic,
                
              ],
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
        } catch ( InvalidArgumentException $e ) {}
        
        verify(
            "The typed collection should throw an InvalidArgumentException "
                ."when a user attempts to set an invalid item. ",
            $exception
          )->isNotEmpty();
        
        
        // Same as above, but for appending
        $exception = null;
        try {
            foreach( $bogus_items as $item ) {
                $collection[] = $item;
            }
        } catch ( InvalidArgumentException $exception ) {}
        
        verify(
            "The typed collection should throw an InvalidArgumentException "
                ."when a user attempts to append an invalid item. ",
            $exception
          )->isNotEmpty();    
            
    }
    
    /**
     * @dataProvider getScenarios
     */
    public function testAcceptsItemsByArrayAccess( Vinyl\Collection\Typed $collection, $accepted )
    {
        $collection['bogus_test_key'] = $accepted;
        $collection[] = $accepted;
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
        } catch ( InvalidArgumentException $exception ) {}
        
        verify_that(
            "A typed collection should throw an InvalidArgumentException "
                ."when passed invalid items as constructor arguments. ",
            !empty($exception)
          );
    }
    
    public function testRejectsEmptyValidator()
    {
        try {
            new Vinyl\Collection\Typed();
        } catch ( Throwable $error ) {}
        
        verify_that(
            "A typed collection should throw an error "
                ."when instantiated without a validator callable. ",
            !empty($error)
          );
    }
    
    public function testAcceptsInitialEmptiness()
    {
        new Vinyl\Collection\Typed( function( int $item ) {}, [] );
        new Vinyl\Collection\Typed( function( int $item ) {} );
    }
    
    public function testAcceptsInitialItems()
    {
        $items = [ 9, 12, 99999999, ];
        $collection = new Vinyl\Collection\Typed( function( int $item ) {}, $items );
        
        verify( 
            "A typed collection should accept items provided as constructor arguments. ",
            (array) $collection
          )->Equals($items);
    }
        
    public function testBindsClosures()
    {
        $string = 'test string';
        
        $validator = function( int $item ) { $this->test_prop = 'test string'; };
        
        $collection = new Vinyl\Collection\Typed( $validator, [ 9 ] );
        
        verify_that(
            "A typed array should bind its validator closure to itself. ",
            !empty($collection->test_var)
          );
    }
    
    
}
