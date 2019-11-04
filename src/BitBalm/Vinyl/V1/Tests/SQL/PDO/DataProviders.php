<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\SQL\PDO;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Collection\PDOs;


trait DataProviders
{    
    public function getPDOs() : PDOs 
    {
        $pdos = [
            new Vinyl\Tests\SQL\PDO\SQLite,
            new Vinyl\Tests\SQL\PDO\MySQL,
            #TODO: new PDO\PostgreSQL,
          ];
        return new PDOs($pdos);
    }
    
    public function getSchemas() : array
    {
        $schemas = [ 
            new Vinyl\Tests\SQL\PDO\Schema\PeopleArticles,
          ];
        return $schemas;
    }
    
    public function getRecordProducers()
    {
        $producers = [
            new Vinyl\RecordProducer\PDO\Statement( new Vinyl\Record\Generic ),
            #TODO: Vinyl\RecordProducer\Caching
            #TODO: Vinyl\Collection\Records
          ];
        return $producers ;
    }
    
}
