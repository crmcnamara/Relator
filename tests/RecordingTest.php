<?php

namespace BitBalm\Relator\Tests;

use PDO;
use Exception;
use InvalidArgumentException;

use PHPUnit\Framework\TestCase;
require_once __DIR__ .'/SqliteTestCase.php';

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Mapper;
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
class RecordingTest extends SqliteTestCase
{
    protected $generic_person;
    protected $generic_article;
    protected $custom_person;
    protected $custom_article;

    public function setUp()
    {
        $pdo = $this->getPdo();
        
        $mapper = $this->getMapper();
        
        // configure two generic records for each entity type
        $this->generic_person   = (new Record\Generic( 'person' ))
            ->setMapper($mapper);
        $this->generic_article  = (new Record\Generic( 'article' ))
            ->setMapper($mapper);

        // Now configure the same thing using anonymous classes that make use of Record/Trait
        $this->custom_person = (new class() implements Record { use RecordTrait; })
            ->setTableName('person')
            ->setMapper($mapper);
        
        $this->custom_article = (new class() implements Record { use RecordTrait; })
            ->setTableName('article')
            ->setMapper($mapper);
        
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
    
    
    public function testRejectsChangingTableNames()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->custom_article->setTableName('address');
    }
    
    public function testRejectsChangingRecorders()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->custom_article->setRecorder(clone $this->recorder);
    }
}
