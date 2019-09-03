<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore\SQL\PDO;

use PDO as PDOConnection;

use Atlas\Pdo\Connection;

use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;
use BitBalm\Vinyl\V1\RecordStore\SQL\PDO\Atlas\Factory as AtlasFactory;


class Atlas extends Vinyl\Tests\RecordStore\SQL\PDO
{
    /**
     * returns a RecordStore instance and fixture record id
     *    for every table of every schema, using each PDO instance.
     */
    public function getRecordStoreScenarios() : array
    {
        $scenarios = [];
        
        foreach ( $this->getSchemas() as $schema ) {
            foreach ( $this->getPDOs() as $pdo ) {
                
                $schema->injectSchema($pdo);
                $record_ids = $schema->injectRecords($pdo);
                
                foreach ( $record_ids as $table => $record_id ) {
                    $scenarios[ implode( ' ', [ $table, get_class($schema), get_class($pdo), ] ) ] = [
                        new Vinyl\RecordStore\SQL\PDO\Atlas( 
                            $table, 
                            new AtlasFactory( Connection::new($pdo) ),
                            new Vinyl\Record\Generic,
                            new Vinyl\Collection\Records
                          ),
                        $record_id,
                      ];
                }
            }
        }
        
        return $scenarios;
    }
}
