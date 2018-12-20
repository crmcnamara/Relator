<?php

namespace BitBalm\Relator\Tests;


use Exception;
use InvalidArgumentException;

use PDO;

use PHPUnit\Framework\TestCase;
require_once __DIR__ .'/SqliteTestCase.php';

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Mapper;
use BitBalm\Relator\Recorder;
use BitBalm\Relator\Recorder\RecordNotFound;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\Mappable\TableNameAlreadySet;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recordable\RecordableTrait;
use BitBalm\Relator\Recordable\RecorderAlreadySet;
use BitBalm\Relator\PDO\SchemaValidator;
use BitBalm\Relator\Mapper\PDO\SchemaValidator\InvalidTable;

use BitBalm\Relator\Tests\Mocks\Person;
use BitBalm\Relator\Tests\Mocks\RecordableArticle;


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
        $this->generic_person   = (new Record\Generic( 'person'  ))->setMapper($mapper);
        $this->generic_article  = (new Record\Generic( 'article' ))->setMapper($mapper);

        // Now configure the same thing using anonymous classes that make use of Record/Trait
        $this->custom_person = (new Person)
            ->setTableName('person')
            ->setMapper($mapper);
        
        $this->custom_article = (new RecordableArticle)
            ->setTableName('article')
            ->setRecorder($mapper);
        
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
    public function testLoadArticle( /*string*/ $article_varname ) 
    {
          
        $expected_article_values = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
          
        // check update id
        foreach ( $expected_article_values as $expected_values ) {
          
            $article = $this->$article_varname->newRecord()->loadRecord($expected_values['id']);
            
            $this->assertEquals( $expected_values, $article->asArray(),
                "A loaded article was not as expected. "
              );
              
            $this->assertEquals( $expected_values['id'], $article->getUpdateId(),
                "An update id for a loaded article was not as expected. "
              );
        }
        
    
        
    }
    
    
    /** 
     * @dataProvider articles
     */
    public function testEditArticle( /*string*/ $article_varname ) 
    {
        $article = $this->$article_varname->newRecord()->loadRecord(2);
        
        $expected_fixture = [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        $this->assertEquals( $expected_fixture, $article->asArray(), 
            "An article fixture for the update test was not as expected. "
          );
        $this->assertEquals( $expected_fixture['id'], $article->getUpdateId(),
            "An update id for an article fixture for the edit test was not as expected. "
          );
          
        $update_values = [ 'id' => 77, 'title' => 'Something or Other Abandoned', ];
        $article->setValues($update_values);
        
        $expected_values = array_replace( $expected_fixture, $update_values );
        
        $this->assertEquals( $expected_values, $article->asArray(), 
            "After updating values with setValues(), the results of asArray() were not as expected. "
          );
          
        $this->assertEquals( $expected_fixture['id'], $article->getUpdateId(),
            "An update id for an article fixture for the edit test was changed after changing it with setValues(). "
          );
        
    }
    
    /** 
     * @dataProvider articles
     */
    public function testLoadArticles( /*string*/ $article_varname ) 
    {
        $articles = $this->$article_varname->newRecord()->loadRecords([ 2, 3, 4, ]);
        
        $expected_articles = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
        
        //check values
        $this->assertEquals( $expected_articles, $articles->asArrays(),
            "Loaded articles were not as expected. "
          );
          
        // check update ids
        foreach ( $expected_articles as $article_idx => $expected_values ) {              
            $this->assertEquals( $expected_values['id'], $articles[$article_idx]->getUpdateId(),
                "An update id for a loaded article was not as expected. "
              );
        }
        
    }
    
    /**
     * Tests updating records loaded from the db. 
     * 
     * @dataProvider articles
     */

    public function testUpdateLoadedArticles( /*string*/ $article_varname ) 
    {
      
        $update_article_id  = 2;
        $new_article_id     = 4;
        $article = $this->$article_varname->newRecord()->loadRecord($update_article_id);
        
        $expected_fixture = [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        $this->assertEquals( $expected_fixture, $article->asArray(), 
            "An article fixture for the update test was not as expected. "
          );
        $this->assertEquals( $update_article_id, $article->getUpdateId(),
            "An update id for an article fixture for the update test was not as expected. "
          );
        
        
        $article->setValues([ 'id' => $new_article_id, 'title' => 'I Forget', 'author_id' => 3, ]);
                
        $article->saveRecord();
        
        
        $articles = [] ;
        foreach ( [ 1, 2, 3, 4, ] as $idx ) {
            try { 
                $articles[$idx] = $this->$article_varname->newRecord()->loadRecord($idx)->asArray();
            } catch ( RecordNotFound $e ) {
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
        
        // check values
        $this->assertEquals( $expected_articles, $articles,
            var_export($articles,1) ."\n".
            "The database state was not as expected after updating an article (including its id). "
          );
          
        // check update id
        $this->assertEquals( $new_article_id, $article->getUpdateId(),
            "The update id for an article that updated it's id via setValues() and SaveRecord() was not as updated. "
          );
        
    }
    
    /**
     * Tests updating records populated from sources other than the db
     * 
     * @dataProvider articles
     */
    public function testUpdatePopulatedArticles( /*string*/ $article_varname ) 
    {
        $update_article_id  = 2;
        $new_article_id     = 4;
        
        $article = $this->$article_varname->newRecord()->setValues(
            [ 'id' => $new_article_id, 'title' => 'I Forget', 'author_id' => 3, ]
          );
        
        $article->saveRecord($update_article_id);
        
        $article_values = [] ;
        foreach ( [ 1, 2, 3, 4, ] as $idx ) {
            try { 
                $articles[$idx] = $this->$article_varname->newRecord()->loadRecord($idx);
                $article_values[$idx] = $articles[$idx]->asArray();
            } catch ( RecordNotFound $e ) {
                $article_values[$idx] = [ 'Exception' => [ 'message' => $e->getMessage() ] ];
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
        
        $this->assertEquals( $expected_articles, $article_values,
            var_export($article_values,1) ."\n".
            "The database state was not as expected after updating an article (including its id). "
          );
          
        // check update id
        $this->assertEquals( $new_article_id, $article->getUpdateId(),
            "The update id for an article that updated it's id via setValues() and SaveRecord() was not as updated. "
          );
          
    }
    
    /**
     * @dataProvider articles
     */
    public function testInsertArticle( /*string*/ $article_varname )
    {
        $article_with_id = $this->$article_varname->newRecord()->setValues([ 
            'id' => '5', 'title' => 'I Forget', 'author_id' => '3',
          ])->insertRecord();
          
        $article_without_id = $this->$article_varname->newRecord()->setValues([ 
            'title' => 'Wandering', 'author_id' => '3',
          ])->saveRecord();
        
        $articles = [] ;
        foreach ( [ 3, 4, 5, 6, 7, ] as $idx ) {
            try { 
                $articles[$idx] = $this->$article_varname->newRecord()->loadRecord($idx)->asArray();
            } catch ( RecordNotFound $e ) {
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
    public function testDeleteLoadedArticle( /*string*/ $article_varname )
    {
        $article = $this->$article_varname->newRecord()->loadRecord(2);
        
        $expected_article = [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        
        $this->assertEquals( $expected_article, $article->asArray(), 
            "Loaded fixture article #2 was not as expected. "
          );
          
        $article->deleteRecord();
        
        $e = null;
        try { $deleted_article = $this->$article_varname->newRecord()->loadRecord(2); }
        catch ( RecordNotFound $e ) {}
        
        $this->assertInstanceOf( RecordNotFound::class, $e, "An expected exception was not thrown. " );
        
    }
    
    /**
     * @dataProvider articles
     */
    public function testDeletePopulatedArticle( /*string*/ $article_varname )
    {
        $article_id = 2;
        
        $fixture_article = $this->$article_varname->newRecord()->loadRecord($article_id);
        
        $expected_article = [ 'id' => $article_id, 'title' => 'Something or Other Revisited', 'author_id' => '2', ];
        
        $this->assertEquals( $expected_article, $fixture_article->asArray(), 
            "Loaded fixture article #2 was not as expected. "
          );
        
        $article = $this->$article_varname->newRecord()->setValues([ 'id' => $article_id ]);  
        $article->deleteRecord();
        
        $e = null;
        try { $deleted_article = $this->$article_varname->newRecord()->loadRecord($article_id); }
        catch ( RecordNotFound $e ) {}
        
        $this->assertInstanceOf( RecordNotFound::class, $e, "An expected exception was not thrown. " );
        
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
    public function testRejectsMissingTable( /*string*/ $article_varname, /*string*/ $method ) 
    {
        $this->pdo->exec( "DROP table article ; ");
        $this->$article_varname->getRecorder()->getValidator()->refreshSchema();
        
        $e = null;
        try { 
          $article = $this->$article_varname->newRecord(); 
          $this->$article_varname->getRecorder()->$method( $article, [2] );
        }
        catch ( InvalidTable $e ) {}
        
        $this->assertInstanceOf( InvalidTable::class, $e, "An expected exception was not thrown. " );
        
    }
    
    
    public function testRejectsChangingTableNames()
    {
        $e = null;
        try { $this->custom_article->setTableName('address'); }
        catch ( TableNameAlreadySet $e ) {}
        
        $this->assertInstanceOf( TableNameAlreadySet::class, $e, "An expected exception was not thrown. " );
    }
    
    public function testRejectsChangingRecorders()
    {
        $e = null;
        try { $this->custom_article->setRecorder(clone $this->recorder); }
        catch ( RecorderAlreadySet $e ) {}
        
        $this->assertInstanceOf( RecorderAlreadySet::class, $e, "An expected exception was not thrown. " );
    }
}
