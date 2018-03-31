<?php

namespace BitBalm\Relator\Tests;

use PHPUnit\Framework\TestCase;
use PDO;

use BitBalm\Relator\Relator;
use BitBalm\Relator\Record;

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

        $pdo = new PDO( 'sqlite::memory:', null, null, [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ] );

        $pdo->exec( "

            CREATE TABLE person   (id INTEGER NOT NULL, name  VARCHAR(255) DEFAULT '' NOT NULL, PRIMARY KEY(id));
            INSERT INTO person VALUES(1,'Joe Josephson');
            INSERT INTO person VALUES(2,'Dave Davidson');
            
            CREATE TABLE article  (id INTEGER NOT NULL, title VARCHAR(255) DEFAULT '' NOT NULL, author_id INTEGER NOT NULL, PRIMARY KEY(id));
            INSERT INTO article VALUES(1,'On Something or Other',1);
            INSERT INTO article VALUES(2,'Something or Other Revisited',2);
            INSERT INTO article VALUES(3,'Counterpoint',2);
            
          ");

        $relator = new Relator\PDO( $pdo );

        $person   = (new Record\Generic('person'))   ->setRelator($relator) ;
        $article  = (new Record\Generic('article'))  ->setRelator($relator) ;

        $person   ->addRelationship( 'id',        $article, 'author_id',  'articles'  ) ;
        $article  ->addRelationship( 'author_id', $person,  'id',         'author'    ) ;
        
        $this->pdo      = $pdo;
        $this->relator  = $relator;
        $this->person   = $person;
        $this->article  = $article;
        
        
    }
    
    public function tearDown() 
    {
        unset( $this->pdo, $this->relator, $this->person, $this->article );
    }
    

    public function testRelatePersonToArticles() 
    {
        
        $person = $this->person->createFromArray(['id'=>2,'name'=>'Dave',]);

        $articles = $person->getRelated('articles');
        
        $expected = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
        
        foreach ( $articles as $idx => $article ) {
            $this->assertEquals( $expected[$idx], $article->asArray() );            
        }
    }


    public function testRelateArticleToAuthor() 
    {
        $article = $this->article->createFromArray(['id'=>3,'title'=>'Counterpoint','author_id' => 2]);
        
        $authors = $article->getRelated('author');
        
        $expected = [ [ 'id' => 2, 'name' => 'Dave Davidson', ] ];
        
        foreach ( $authors as $idx => $author ) {
            $this->assertEquals( $expected[$idx], $author->asArray() );            
        }
    }

}
