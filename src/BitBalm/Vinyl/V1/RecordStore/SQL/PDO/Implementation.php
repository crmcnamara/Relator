<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use Throwable;
use PDO;
use PDOStatement;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\RecordStore\SQL;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;


trait Implementation /* implements Vinyl\RecordStore\SQL\PDO */
{ 
    use SQL\Implementation;
    
    
    protected $pdo;
    protected $records;
    

    public function __construct( 
        string $table_name, 
        string $primary_key_name, 
        array $field_names = [],
        PDO $pdo,
        Vinyl\Record $record,
        Vinyl\RecordProducer\PDO $records
      )
    {
        $this->pdo              = $pdo;
        $this->records          = $records;
        
        $this->table_name       = $table_name;
        $this->primary_key_name = $primary_key_name;
        $this->field_names      = $field_names;
    }
  
    public function getPDO() : PDO 
    {
        return $this->pdo;
    }
    
    
    /* (partially) implements Vinyl\RecordStore */
    
    public function getRecord( $record_id ) : Record 
    {
        return $this->getRecordByFieldValue( $this->getPrimaryKey(), $record_id );
    }

    
    public function getRecords( array $record_ids ) : Vinyl\RecordProducer
    {
        return $this->getRecordsByFieldValues( $this->getPrimaryKey(), $record_ids );
    }
    
    public function getRecordByFieldValue( string $field, $value ) : Record 
    {
        $records = $this->getRecordsByFieldValues( $field, [ $value ] );
        return $this->getOnlyRecord($records);
    }
    
    
    /* completes implementation of Vinyl\RecordStore\SQL\PDO */
    
    public function getRecordsByQueryString( string $query, array $parameters ) : Vinyl\RecordProducer
    {
        try {
            $statement = $this->pdo->prepare($query);
            return $this->getRecordsByStatement( $statement, $parameters ); 
            
        } catch ( Throwable $x ) { 

            $x->caused_by = [
                'query_string'  => $query,
                'parameters'    => $parameters,
              ];
            throw new $x( 
                $x->getMessage() ."\n". var_export([$x->caused_by],true)
              );
        }
    }
    
    protected function execute( string $query, array $parameters ) : int 
    {
        try {
            $statement = $this->pdo->prepare($query);
            $statement->execute($parameters);
            return $statement->rowCount();
            
        } catch ( Throwable $x ) { 
            $x->caused_by = [
                'query_string'  => $query,
                'parameters'    => $parameters,
              ];
            throw new $x( 
                $x->getMessage() ."\n". var_export([$x->caused_by],true)
              );
        }
    }
    
    /* implements Vinyl\RecordStore\SQL\PDO */

    public function getRecordByStatement( PDOStatement $statement, array $parameters ) : Record 
    {
        $records = $this->getRecordsByStatement( $statement, $parameters );
        return $this->getOnlyRecord($records);
    }
    
    public function getRecordsByStatement( PDOStatement $statement, array $parameters = [] ) : Vinyl\RecordProducer
    {
        // Some statements may already be executed - 
        //    execute it ourselves only if provided parameters, or if it hasn't been executed.
        if ( !empty($parameters) or $statement->columnCount() == 0 ) { 
            $statement->execute($parameters); 
        }
        
        $records = $this->records->withStatement($statement);

        return $records;
    }
    
}
