<?php

namespace BitBalm\Relator\Tests;

use PDO;

use PHPUnit\Framework\TestCase;

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Relator;
use BitBalm\Relator\Record;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\GetsRelatedRecords;
use BitBalm\Relator\Relatable;
use BitBalm\Relator\Relatable\RelatableTrait;
use BitBalm\Relator\PDO\SchemaValidator;
use BitBalm\Relator\RecordSet;


/**
 * @runTestsInSeparateProcesses
 */
class RelatingTests extends TestCase
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

        $this->relator = new Relator\PDO( 
            $this->pdo, 
            new SchemaValidator( new SqliteSchema( $this->pdo, new ColumnFactory ) )
          );

        // configure two generic records for each entity type
        $this->generic_person   = (new Record\Generic('person', 'id'))   ->setRelator($this->relator) ;
        $this->generic_article  = (new Record\Generic('article', 'id'))  ->setRelator($this->relator) ;

        // and define the relationships between them
        $this->generic_person   ->addRelationship( 'id',        $this->generic_article, 'author_id',  'articles'  ) ;
        $this->generic_article  ->addRelationship( 'author_id', $this->generic_person,  'id',         'author'    ) ;


        // Now configure the same thing using anonymous classes that make use of RecordTrait
        $this->custom_person = new class() implements Relatable, GetsRelatedRecords {
            use RelatableTrait;
            public function getTableName() : string { return 'person'; }
        };
        $this->custom_person->setRelator($this->relator) ;
        
        $this->custom_article = new class() implements Relatable, GetsRelatedRecords {
            use RelatableTrait;
            public function getTableName() : string { return 'article'; }
        };
        $this->custom_article->setRelator($this->relator) ;
        
        $this->custom_person   ->addRelationship( 'id',        $this->custom_article, 'author_id',  'articles'  ) ;
        $this->custom_article  ->addRelationship( 'author_id', $this->custom_person,  'id',         'author'    ) ;

    }
    
    public function tearDown() 
    {
        unset(
            $this->pdo,
            $this->relator,
            $this->generic_person,
            $this->custom_person,
            $this->generic_article,
            $this->custom_article
          );
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
        $authors = new RecordSet\Relatable([
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

}
