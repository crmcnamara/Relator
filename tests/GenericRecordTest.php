<?php

namespace BitBalm\Relator\Tests;

use PDO;

use PHPUnit\Framework\TestCase;
require_once __DIR__ .'/SqliteTestCase.php';

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Relator;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\PDO\SchemaValidator;
use BitBalm\Relator\RecordSet;
use BitBalm\Relator\RecordSet\GetsRelated;


/**
 * @runTestsInSeparateProcesses
 */
class GenericRecordTest extends SqliteTestCase
{
    protected $generic_person;
    protected $generic_article;


    public function setUp()
    {
        $pdo = $this->getPdo();
        
        $mapper = $this->getMapper();

        // configure two generic records for each entity type
        $this->generic_person   = (new Record\Generic( 'person'  ))->setMapper($mapper);
        $this->generic_article  = (new Record\Generic( 'article' ))->setMapper($mapper);

        // and define the relationships between them
        $this->generic_person   ->addRelationship( 'id',        $this->generic_article, 'author_id',  'articles'  );
        $this->generic_article  ->addRelationship( 'author_id', $this->generic_person,  'id',         'author'    );

    }
    
    public function tearDown() 
    {
        unset(
            $this->recorder,
            $this->generic_person,
            $this->custom_person,
            $this->generic_article,
            $this->custom_article
          );
        return parent::tearDown();
    }


    /* A variety of setter methods reject changes, making their values immutable once set. 
     * These values are stored statically, per-class rather than per instance.
     * Record\Generic stores these static values as arrays, segregated by table name.
     * The following tests insure that that segragation happens as expected.
     */ 
    public function testGenericSegregatesTableNames()
    {
        $address = new Record\Generic( 'address' );
        $number  = new Record\Generic( 'number' );
        
        $this->assertEquals( 'address', $address->getTableName(), 
            "A table name was not stored correctly in a Generic Record. " 
          );
        $this->assertEquals( 'number', $number->getTableName(), 
            "A table name was not stored correctly in a Generic Record. " 
          );
    }

    public function testGenericSegregatesRelators()
    {
        $this->generic_article  = (new Record\Generic( 'address' ))
            ->setRelator(clone $this->relator);
        
        $this->assertTrue(true);
    }
    
    public function testGenericSegregatesRecorders()
    {
        $this->generic_article  = (new Record\Generic( 'address' ))
            ->setRecorder(clone $this->recorder);
                
        $this->assertTrue(true);
    }
    
    public function testGenericSegregatesRelationships()
    {
        $this->generic_person    ->addRelationship( 'id',        $this->generic_person, 'id',  'self'  );
        $this->generic_article   ->addRelationship( 'id',        $this->generic_person, 'id',  'self'  );
        
        $this->assertTrue(true);
    }
}
