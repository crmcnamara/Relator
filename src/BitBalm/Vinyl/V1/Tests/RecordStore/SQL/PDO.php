<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\RecordStore\SQL;

use PDO as PDOConnection;


use BitBalm\Vinyl\V1 as Vinyl;


class PDO extends Vinyl\Tests\RecordStore\SQL
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
                        new Vinyl\RecordStore\SQL\PDO( $table, $pdo ),
                        $record_id,
                      ];
                }
            }
        }
        
        return $scenarios;
    }
    

    public function getPDOs() : array 
    {
        $pdos = [
            new PDO\SQLite,
            #TODO: new PDO\MySQL,
            #TODO: new PDO\PostgreSQL,
          ];
        return $pdos;
    }
    
    public function getSchemas() : array
    {
        $schemas = [ 
            new PDO\Schema\PeopleArticles,
          ];
        return $schemas;
    }
    
    # compare getRecord/s/ByStatement( getSelectStatement() ) to getRecord/s/ByFieldValues()
    
}
