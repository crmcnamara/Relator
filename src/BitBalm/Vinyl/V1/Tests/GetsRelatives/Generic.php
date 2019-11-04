<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\GetsRelatives;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Configuration;


use BitBalm\Vinyl\V1 as Vinyl;


class Generic extends Vinyl\Tests\GetsRelatives
{
    use Vinyl\Tests\SQL\PDO\DataProviders;
    
    
    public function getRelativeScenarios() : array 
    {
        $scenarios = [];
    
        foreach ( $this->getSchemas() as $schema ) {
            foreach ( $this->getPDOs() as $pdo ) {
              
                $schema->injectSchema($pdo);
                $record_ids   = $schema->injectRecords($pdo);
                
                $connection = DriverManager::getConnection( [ 'pdo' => $pdo ], new Configuration );
                
                $relator = new Vinyl\Relator\Simple;
                
                $one  = new class( $relator ) extends Vinyl\Record\GetsRelatives {};
                $one_table  = $schema->getOneToManySourceTable();
                $one_store = new Vinyl\RecordStore\SQL\PDO\Doctrine(
                    $one_table,
                    $connection->createQueryBuilder(),
                    new Vinyl\RecordProducer\PDO\Statement($one)
                  );
                  
                $many = new class( $relator ) extends Vinyl\Record\GetsRelatives {};
                $many_table = $schema->getManyToOneSourceTable();
                $many_store = new Vinyl\RecordStore\SQL\PDO\Doctrine(
                    $many_table,
                    $connection->createQueryBuilder(),
                    new Vinyl\RecordProducer\PDO\Statement($many)
                  );
                
                $many_field = $schema->getManyToOneSourceField();
                
                $relator
                    ->setRelationship( get_class($many), new Vinyl\Relationship\Simple(
                        get_class($one), 
                        $one_store->getPrimaryKey(),
                        $many_store, 
                        $many_field
                        
                      ) )
                    ->setRelationship( get_class($one), new Vinyl\Relationship\Simple(
                        get_class($many), 
                        $many_field,
                        $one_store, 
                        $one_store->getPrimaryKey()
                      ) );
                
                $title = implode( ' ', [ 
                    $this->abbreviateClass($pdo), 
                    $this->abbreviateClass($schema), 
                  ] );
                  
                $scenarios['one_to_many'][$title] = [ $one_store ->getRecord( $record_ids[$one_table]  ), get_class($many) ];
                $scenarios['many_to_one'][$title] = [ $many_store->getRecord( $record_ids[$many_table] ), get_class($one)  ];
            }
        }
        
        return $scenarios;
    }

    public function getOneToManyScenarios() : array 
    {
        return $this->getRelativeScenarios()['one_to_many'];
    }

    public function getManyToOneScenarios() : array 
    {
        return $this->getRelativeScenarios()['many_to_one'];
    }
    
}
