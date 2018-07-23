<?php

namespace BitBalm\Relator\Tests;

use InvalidArgumentException;

use PDO;

use PHPUnit\Framework\TestCase;
require_once __DIR__ .'/SqliteTestCase.php';

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Relator;
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
class RelatingTest extends SqliteTestCase
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

        // and define the relationships between them
        $this->generic_person   ->addRelationship( 'id',        $this->generic_article, 'author_id',  'articles'  );
        $this->generic_article  ->addRelationship( 'author_id', $this->generic_person,  'id',         'author'    );


        // Now configure the same thing using anonymous classes that make use of Record/Trait
        $this->custom_person = (new class() implements Record { use RecordTrait; })
            ->setTableName('person')
            ->setMapper($mapper);
        
        $this->custom_article = (new class() implements Record { use RecordTrait; })
            ->setTableName('article')
            ->setMapper($mapper);
        
        $this->custom_person   ->addRelationship( 'id',        $this->custom_article, 'author_id',  'articles'  ) ;
        $this->custom_article  ->addRelationship( 'author_id', $this->custom_person,  'id',         'author'    ) ;

    }
    
    public function tearDown() 
    {
        unset(
            $this->relator,
            $this->generic_person,
            $this->custom_person,
            $this->generic_article,
            $this->custom_article
          );
        return parent::tearDown();
    }

    
    public function people()
    {
        return [ [ 'generic_person' ], [ 'custom_person' ], ];
    }


    /** Tests getting articles authored by a person ( one person to many articles )
     * @dataProvider people
     */
    public function testRelatePersonToArticles( string $person_varname ) 
    {
        
        $person = $this->$person_varname->newRecord()->setValues(['id'=>2,'name'=>'Dave',]);

        $articles = $person->getRelated('articles')->asArrays();
        
        $expected_articles = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
        
        $this->assertEquals( $expected_articles, $articles,
            var_export( $articles, 1 ) ."\n".
            "Articles related to a person were not as expected. "
          );
        
    }


    public function articles()
    {
        return [ [ 'generic_article' ], [ 'custom_article' ], ];
    }


    /** Tests getting the person who authored an article ( one article to one person )
     * @dataProvider articles
     */
    public function testRelateArticleToAuthor( string $article_varname ) 
    {
      
        $article = $this->$article_varname->newRecord()->setValues(['id'=>3,'title'=>'Counterpoint','author_id' => 2]);

        $authors = $article->getRelated('author');
        
        $expected = [ [ 'id' => 2, 'name' => 'Dave Davidson', ] ];
        
        foreach ( $authors as $idx => $author ) {
            $this->assertEquals( $expected[$idx], $author->asArray() );            
        }
        
    }
    
    /** Tests getting articles authored by a person ( one person to many articles )
     * @dataProvider people
     */
    public function testRelateAuthorsToArticles( string $person_varname ) 
    {
        $authors = new RecordSet\GetsRelated([
            $this->$person_varname->newRecord()->setValues(['id'=>1,'name'=>'Joe',]),
            $this->$person_varname->newRecord()->setValues(['id'=>2,'name'=>'Dave',])
          ]);
         
        $articles = $authors->getRelated('articles')->asArrays();
        
        $expected_articles = [ 
            [ 'id' => '1', 'title' => 'On Something or Other', 'author_id' => '1', ],
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
        
        $this->assertEquals( $expected_articles, $articles,
            var_export( $articles, 1 ) ."\n".
            "Articles related to a RecordSet of multiple people were not as expected. "
          );
        
    }
    
    
    public function testRejectsChangingTableNames()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->custom_article->setTableName('address');
    }

    public function testRejectsChangingRelators()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->custom_article->setRelator(clone $this->relator);
    }
    
    public function testRejectsChangingRelationships()
    {
        $this->custom_person    ->addRelationship( 'id',        $this->generic_person, 'id',  'self'  );
        
        // add a 'self' relation to a different class to insure it is accepted.
        $this->custom_article   ->addRelationship( 'id',        $this->generic_person, 'id',  'self'  );
        
        $this->expectException(InvalidArgumentException::class);
        $this->custom_person    ->addRelationship( 'id',        $this->generic_person, 'id',  'self'  );
        
    }


}
