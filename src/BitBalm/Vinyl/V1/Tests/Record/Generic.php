<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\Record;


use BitBalm\Vinyl\V1 as Vinyl;


class Generic extends Vinyl\Tests\Record
{
    
    public function getRecords() : array 
    {
      
        $record = new Vinyl\Record\Generic;
        $record->initializeRecord( 9, [ 'id' => 9, 'name' => 'Kelly' ] );

        return [ $record ];
    }
    
    
}
