#!/usr/bin/php
<?php

require_once __DIR__ .'/../vendor/autoload.php' ;

use BitBalm\Relator\PDO\Relator;
use BitBalm\Relator\SimpleRelationship;
use BitBalm\Relator\PDO\GenericRecord;

$relator = 
    ( new Relator( new PDO( 'sqlite::memory:' ) ) )
        ->addRelationships([
            new SimpleRelationship( 
                $person   = new GenericRecord('person',  ['id'=>1,'name'=>'Joe',] ), 'id', 
                $article  = new GenericRecord('article', ['id'=>1,'title'=>'On something',] ), 'author_id'
              ),
          ]);



