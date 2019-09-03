<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore\SQL\PDO;

use PDO as PDOConnection;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;
use BitBalm\Vinyl\V1\Exception\TooManyRecords;


class Doctrine extends Vinyl\Tests\RecordStore\SQL\PDO
{
    /**
     * returns a RecordStore instance and fixture record id
     *    for every table of every schema, using each PDO instance.
     */
    public function getRecordStoreScenarios() : array
    {
        try {
            $scenarios = [];
            
            foreach ( $this->getSchemas() as $schema ) {
                foreach ( $this->getPDOs() as $pdo ) {
                    
                    $schema->injectSchema($pdo);
                    $record_ids = $schema->injectRecords($pdo);
                    
                    foreach ( $record_ids as $table => $record_id ) {
                        $scenarios[ implode( ' ', [ $table, get_class($schema), get_class($pdo), ] ) ] = [
                            new Vinyl\RecordStore\SQL\PDO\Doctrine( 
                                $table, 
                                DriverManager::getConnection( [ 'pdo' => $pdo ], new Configuration )->createQueryBuilder(),
                                new Vinyl\Record\Generic,
                                new Vinyl\Collection\Records
                              ),
                            $record_id,
                          ];
                    }
                }
            }
            
            return $scenarios;
            
        // PHPUnit isn't very forthcoming with error info in data providers
        } catch ( \Throwable $x ) {
            throw new \Exception((string)$x);
        }
    }
}
