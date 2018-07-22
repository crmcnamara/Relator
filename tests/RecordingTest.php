<?php

namespace BitBalm\Relator\Tests;

use PDO;
use Exception;
use InvalidArgumentException;

use PHPUnit\Framework\TestCase;

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Recorder;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Mappable\MappableTrait;
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
        $this->generic_person   = (new class extends Record\Generic { use RecordTrait; })
            ->setTableName('person')
            ->setPrimaryKeyName('id')
            ->setRecorder($this->recorder);
        $this->generic_article  = (new class extends Record\Generic { use RecordTrait; })
            ->setTableName('article')
            ->setPrimaryKeyName('id')
            ->setRecorder($this->recorder);

        // Now configure the same thing using anonymous classes that make use of RecordTrait
        $this->custom_person = (new class() implements Record { use RecordTrait; })
            ->setTableName('person')
            ->setPrimaryKeyName('id')
            ->setRecorder($this->recorder);
        
        $this->custom_article = (new class() implements Record { use RecordTrait; })
            ->setTableName('article')
            ->setPrimaryKeyName('id')
            ->setRecorder($this->recorder);
        
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
    public function testLoadArticle( string $article_varname ) 
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
    public function testLoadArticles( string $article_varname ) 
    {
        $articles = $this->$article_varname->newRecord()->loadRecords([ 2, 3, 4, ])->asArrays();
        
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
        
        $articles = [] ;
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
    public function testInsertArticle( string $article_varname )
    {
        $article_with_id = $this->$article_varname->newRecord()->setValues([ 
            'id' => '5', 'title' => 'I Forget', 'author_id' => '3',
          ])->saveRecord();
          
        $article_without_id = $this->$article_varname->newRecord()->setValues([ 
            'title' => 'Wandering', 'author_id' => '3',
          ])->saveRecord();
        
        $articles = [] ;
        foreach ( [ 3, 4, 5, 6, 7, ] as $idx ) {
            try { 
                $articles[$idx] = $this->$article_varname->newRecord()->loadRecord($idx)->asArray();
            } catch ( InvalidArgumentException $e ) {
                $articles[$idx] = [ 'Exception' => [ 'message' => $e->getMessage() ] ];
            }
        }
        
        $expected_articles = [
            3 => 
            array (
              'id' => '3',
              'title' => 'Counterpoint',
              'author_id' => '2',
            ),
            4 => 
            array (
              'Exception' => 
              array (
                'message' => 'No article records found for id: 4 ',
              ),
            ),
            5 => 
            array (
              'id' => '5',
              'title' => 'I Forget',
              'author_id' => '3',
            ),
            6 => 
            array (
              'id' => '6',
              'title' => 'Wandering',
              'author_id' => '3',
            ),
            7 => 
            array (
              'Exception' => 
              array (
                'message' => 'No article records found for id: 7 ',
              ),
            ),

          ];
        
        $this->assertEquals( $expected_articles, $articles,
            var_export($articles,1) ."\n".
            "The database state was not as expected after inserting articles. "
          );
        
    }
    
    /**
     * @dataProvider articles
     */
    public function testDeleteArticle( string $article_varname )
    {
        $article = $this->$article_varname->newRecord()->loadRecord(2);
        
        $expected_article = [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        
        $this->assertEquals( $expected_article, $article->asArray(), 
            "Loaded fixture article #2 was not as expected. "
          );
          
        $article->deleteRecord(); $article->deleteRecord();
        
        $e = null;
        try {
            $deleted_article = $this->$article_varname->newRecord()->loadRecord(2);
            
        } catch ( InvalidArgumentException $e ) {}
        
        $this->assertNotEmpty( $e, 
            "After deleting article #2 and attempting to reload it, "
            ."an exception indicating its absence was not thrown. "
          );

          
    }
    
    
    public function recorderMethods()
    {
        return [ 
            [ 'loadRecord' ], 
            [ 'loadRecords' ], 
            [ 'saveRecord' ], 
            [ 'deleteRecord' ], 
          ];
    }
    
    public function articlesAndMethods()
    {
        $scenarios = [] ;
        foreach ( $this->articles() as $article ) {
            foreach( $this->recorderMethods() as $method ) {
                $scenarios[] = [ current($article), current($method), ];
            }
        }
        return $scenarios;
    }
    
    /**
     * @dataProvider articlesAndMethods
     */
    public function testRejectsMissingTable( string $article_varname, string $method ) 
    {
        $this->pdo->exec( "DROP table article ; ");
        $this->$article_varname->getRecorder()->getValidator()->refreshSchema();
        
        $this->expectException(InvalidArgumentException::class);
        
        $article = $this->$article_varname->newRecord();
        $this->$article_varname->getRecorder()->$method( $article, [2] );
        
    }
    

}
