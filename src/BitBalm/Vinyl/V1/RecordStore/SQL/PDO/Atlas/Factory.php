<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO\Atlas;

use Atlas\Pdo\Connection;
use Atlas\Info\Info as SchemaInfo;
use Atlas\Query\QueryFactory;

use BitBalm\Vinyl\V1 as Vinyl;


class Factory
{
    protected $connection;
    protected $query_factory;
    protected $info_class;
    protected $schema_info;
    
    
    public function __construct( Connection $connection, QueryFactory $query_factory = null, string $info_class = null )
    {
        $this->connection = $connection;
        $this->query_factory = $query_factory ?: new QueryFactory;
        $this->info_class = $info_class ?: SchemaInfo::class;
    }
    
    public function getConnection() : Connection
    {
        return $this->connection;
    }
    
    public function getQueryFactory() : QueryFactory
    {
        return $this->query_factory;
    }
    
    public function getInfo() : SchemaInfo
    {
        if ( empty($this->schema_info) ) { 
            $class = $this->info_class;
            $this->schema_info = $class::new( $this->getConnection() );
        }
        
        return $this->schema_info;
    }
}
