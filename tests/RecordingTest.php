<?php

namespace BitBalm\Relator\Tests;

use PDO;

use PHPUnit\Framework\TestCase;

use Aura\SqlSchema\SqliteSchema;
use Aura\SqlSchema\ColumnFactory;

use BitBalm\Relator\Recorder;
use BitBalm\Relator\Record;
use BitBalm\Relator\Record\RecordTrait;
use BitBalm\Relator\Recordable;
use BitBalm\Relator\Recordable\RecordableTrait;

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
        
        
        $this->recorder = new Recorder\PDO( $this->pdo, new SqliteSchema( $this->pdo, new ColumnFactory ) );

        // configure two generic records for each entity type
        $this->generic_person   = (new Record\Generic('person', 'id'))   ->setRecorder($this->recorder) ;
        $this->generic_article  = (new Record\Generic('article', 'id'))  ->setRecorder($this->recorder) ;

        // Now configure the same thing using anonymous classes that make use of RecordableTrait
        $this->custom_person = new class() implements Recordable {
            use RecordableTrait;            
            public function getTableName()      : string { return 'person'; }
            public function getPrimaryKeyName() : string { return 'id';     }
        };
        $this->custom_person->setRecorder($this->recorder) ;
        
        $this->custom_article = new class() implements Recordable {
            use RecordableTrait;
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
            $article1 = $this->$article_varname->loadRecord(2),
            $article2 = $this->$article_varname->loadRecord(3),
          ];

        $expected = [ 
            [ 'id' => '2', 'title' => 'Something or Other Revisited', 'author_id' => '2', ], 
            [ 'id' => '3', 'title' => 'Counterpoint',  'author_id' => '2', ],
          ];
        
        foreach ( $articles as $idx => $article ) {
            $this->assertEquals( $expected[$idx], $article->asArray() );            
        }
        
    }



}
