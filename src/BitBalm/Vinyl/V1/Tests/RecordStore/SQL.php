<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore;


use BitBalm\Vinyl\V1 as Vinyl;


abstract class SQL extends Vinyl\Tests\RecordStore
{
    # compare getRecord/s/Query( getSelectQuery() ) to getRecord/s/ByFieldValues()
    
    # compare getRecord/s/ByQueryString( getSelectQuery() ) to getRecord/s/ByFieldValues()
}
