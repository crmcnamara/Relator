<?php

namespace BitBalm\Relator\Tests;

use PDO;
use Exception;
use InvalidargumentException;

use PHPUnit\Framework\TestCase;

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Recorder;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\PDO\SchemaValidator;


/**
 * @runTestsInSeparateProcesses
 */
class RecordingTests extends TestCase
{
    protected $pdo;
    protected $relator;
    protected $person;
    protected $article;

    public function setUp()
    {

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
        
        
        $this->recorder = new Recorder\PDO( 
            $this->pdo, 
            new SchemaValidator( new SqliteSchema( $this->pdo, new ColumnFactory ) )
          );

        // configure two generic records for each entity type
        $this->generic_person   = (new Record\Generic('person', 'id'))   ->setRecorder($this->recorder) ;
        $this->generic_article  = (new Record\Generic('article', 'id'))  ->setRecorder($this->recorder) ;

        // Now configure the same thing using anonymous classes that make use of RecordableTrait
        $this->custom_person = new class() implements Recordable {
            use Recordtrait, RecordableTrait;            
            public function getTableName()      : string { return 'person'; }
            public function getPrimaryKeyName() : string { return 'id';     }
        };
        $this->custom_person->setRecorder($this->recorder) ;
        
        $this->custom_article = new class() implements Recordable {
            use Recordtrait, RecordableTrait;
            public function getTableName()      : string { return 'article';  }
            public function getPrimaryKeyName() : string { return 'id';       }
        };
        $this->custom_article->setRecorder($this->recorder) ;
        
    }
    
    public function tearDown() 
    {
        unset(
            $this->pdo,
            $this->recorder,
            $this->generic_person,
            $this->custom_person,
            $this->generic_article,
            $this->custom_article
          );
    }

    
    public function articles()
    {
        return [ [ 'generic_article' ], [ 'custom_article' ], ];
    }


    /** 
     * @dataProvider articles
     */
    public function testLoadArticles( string $article_varname ) 
    {
        $articles = [
            $this->$article_varname->newRecord()->loadRecord(2)->asArray(),
            $this->$article_varname->newRecord()->loadRecord(3)->asArray(),
          ];

        $expected_articles = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
          
        $this->assertEquals( $expected_articles, $articles,
            "Loaded articles were not as expected. "
          );
        
    }
    
    /**
     * @dataProvider articles
     */
    public function testUpdateArticles( string $article_varname ) 
    {
        $article = $this->$article_varname->newRecord()->loadRecord(2);
        
        $expected_fixture = [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        $this->assertEquals( $expected_fixture, $article->asArray(), 
            "An article fixture for the update test was not as expected. "
          );
        
        $article->setValues([ 'id' => 4, 'title' => 'I Forget', 'author_id' => 3, ]);
        $article->saveRecord();
        
        foreach ( [ 1, 2, 3, 4, ] as $idx ) {
            try { 
                $articles[$idx] = $this->$article_varname->newRecord()->loadRecord($idx)->asArray();
            } catch ( InvalidArgumentException $e ) {
                $articles[$idx] = [ 'Exception' => [ 'message' => $e->getMessage() ] ];
            }
        }
        
        $expected_articles = [
            1 => 
            array (
              'id' => '1',
              'title' => 'On Something or Other',
              'author_id' => '1',
            ),
            2 => 
            array (
              'Exception' => 
              array (
                'message' => 'No article records found for id: 2 ',
              ),
            ),
            3 => 
            array (
              'id' => '3',
              'title' => 'Counterpoint',
              'author_id' => '2',
            ),
            4 => 
            array (
              'id' => '4',
              'title' => 'I Forget',
              'author_id' => '3',
            ),
          ];
        
        $this->assertEquals( $expected_articles, $articles,
            var_export($articles,1) ."\n".
            "The database state was not as expected after updating an article (including its id). "
          );
        
    }
    
    /**
     * @dataProvider articles
     */
    public function testLoadRejectsMissingTable( string $article_varname ) 
    {
        $this->pdo->exec( "DROP table article ; ");
        $this->$article_varname->getRecorder()->getValidator()->refreshSchema();
        
        $this->expectException(InvalidArgumentException::class);
        
        $article = $this->$article_varname->newRecord()->loadRecord(2);
        
    }
    

}
