#!/usr/bin/php
<?php

require_once __DIR__ .'/../vendor/autoload.php' ;

use BitBalm\Relator\PDO\Relator;
use BitBalm\Relator\SimpleRelationship;
use BitBalm\Relator\GenericRecord;


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


$person   = new GenericRecord('person',  ['id'=>1,'name'=>'Joe',] ) ;
$article  = new GenericRecord('article', ['id'=>3,'title'=>'Counterpoint','author_id' => 2] ) ;

$relator = 
    ( new Relator( $pdo ) )
        ->addRelationships([
            'articles' => new SimpleRelationship( 
                $person,  'id',
                $article, 'author_id'
              ),        
            'author' => new SimpleRelationship( 
                $article, 'author_id',
                $person,  'id'
              ),
          ]);


var_dump( (array) $person );
$articles = $person->getRelated('articles');
foreach ( $articles as $articlerecord ) {
  echo "\n";
  vaR_dump( (array) $articlerecord );
}


echo "\n";
var_dump(['(array) $articlerecord' => (array) $articlerecord ]);


var_dump( (array) $article );
$authors = $article->getRelated('author');
var_dump(['$authors' => $authors]);
foreach ( $authors as $personrecord ) {
  echo "\n";
  vaR_dump(['personrecord' => (array) $personrecord ]);
}
