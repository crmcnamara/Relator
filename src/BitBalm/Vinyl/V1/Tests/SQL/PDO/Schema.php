<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Tests\SQL\PDO;

use PDO;

use Phinx\Db\Adapter\AdapterInterface as Adapter;
use Phinx\Config\Config;
use Phinx\Migration\Manager\Environment;


abstract class Schema
{
    /**
     * returns an array of table names
     */
    abstract public function injectSchema( PDO $pdo ) : array ;
    
    /**
     * returns an array of fixture record ids, indexed by table names
     */
    abstract public function injectRecords( PDO $pdo ) : array ;
    
    
    public function getAdapter( PDO $pdo ) : Adapter 
    {
        $config = new Config([ 'environments' => [
            'vinyl_test' => [
                'name' => $pdo->database ?? null,
                'connection' => $pdo,
              ],
          ]]);
          
        $environment = new Environment( 'vinyl_test', $config->getEnvironment('vinyl_test') );
        $adapter = $environment->getAdapter();
        
        return $adapter;

    }
    
}
