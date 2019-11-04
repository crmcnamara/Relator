<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\SQL\PDO\Schema;

use PDO;

use Phinx\Db\Table;
use Phinx\Db\Adapter\AdapterInterface as Adapter;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\Tests\SQL\PDO\Schema;


class PeopleArticles extends Schema
{
    public function injectSchema( PDO $pdo ) : array
    {
        $adapter = $this->getAdapter($pdo);
        
        $table_names = [];
        
        (new Table( $table_names[] = 'person', [], $adapter ))
            ->addColumn( 'first_name', 'string', [ 'limit' => 64, ] )
            ->addColumn( 'last_name',  'string', [ 'limit' => 64, ] )
            ->create();
            
        (new Table( $table_names[] = 'article', [], $adapter )) 
            ->addColumn( 'title',  'string', [ 'limit' => 256, ] )
            ->addColumn( 'author_id',  'integer' )
            ->create();
            
        return $table_names;

    }
    
    public function injectRecords( PDO $pdo ) : array
    {
        $adapter = $this->getAdapter($pdo);
        
        $fixture_record_ids = [];
        
        (new Table( 'person', [], $adapter ))->insert([
            [ 'id' => 3, 'first_name' => 'Buck',  'last_name' => 'Winfield', ],
            [ 'id' => 4, 'first_name' => 'Kelly', 'last_name' => 'Stafford', ],
          ])->save();
        $fixture_record_ids['person'] = 3;
            
        (new Table( 'article', [], $adapter ))->insert([
            [ 'id' => 5, 'title' => 'Something or Other',           'author_id' => 3, ],
            [ 'id' => 6, 'title' => 'Something or Other Revisited', 'author_id' => 3, ],
          ])->save();
        $fixture_record_ids['article'] = 5;
        
        return $fixture_record_ids;
    }
    
    
    public function getOneToManySourceTable() : string 
    {
        return 'person';
    }
    
    public function getManyToOneSourceTable() : string
    {
        return 'article';
    }
    
    public function getManyToOneSourceField() : string
    {
        return 'author_id';
    }
    
}
