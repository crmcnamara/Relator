<?php

namespace BitBalm\Relator\Tests;

use PDO;

use PHPUnit\Framework\TestCase;

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Mapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Relator;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\Mapper\PDO\SchemaValidator;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\RecordSet\GetsRelated;


/**
 * @runTestsInSeparateProcesses
 */
abstract class SqliteTestCase extends TestCase
{
    protected $pdo;
    protected $mapper;
    protected $recorder;
    protected $relator;
        

    public function getPdo() : PDO
    {
        if ( $this->pdo ) { return $this->pdo; }
        
        $this->pdo = new PDO( 'sqlite::memory:', null, null, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ] );

        $this->pdo->exec( "

            CREATE TABLE person   (id INTEGER NOT NULL, name  VARCHAR(255) DEFAULT '' NOT NULL, PRIMARY KEY(id));
            INSERT INTO person VALUES(1,'Joe Josephson');
            INSERT INTO person VALUES(2,'Dave Davidson');
            
            CREATE TABLE article  (id INTEGER NOT NULL, title VARCHAR(255) DEFAULT '' NOT NULL, author_id INTEGER NOT NULL, PRIMARY KEY(id));
            INSERT INTO article VALUES(1,'On Something or Other',1);
            INSERT INTO article VALUES(2,'Something or Other Revisited',2);
            INSERT INTO article VALUES(3,'Counterpoint',2);
            
          ");
          
        return $this->pdo;
    }
    
    public function tearDown() : void
    {
        unset( $this->pdo, $this->mapper, $this->recorder, $this->relator );
        parent::tearDown();
    }
    
    public function getMapper() : Mapper
    {
        if ( $this->mapper ) { return $this->mapper ; }
        
        $pdo = $this->getPdo();
        
        $this->mapper = new Mapper\PDO( 
            $pdo,
            new SchemaValidator\Aura( new SqliteSchema( $pdo, new ColumnFactory ) )
          );
          
        $this->recorder = $this->relator = $this->mapper;
        
        return $this->mapper;
      
    }

}
