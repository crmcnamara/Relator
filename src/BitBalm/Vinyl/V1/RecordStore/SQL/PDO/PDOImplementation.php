<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use Throwable;
use PDO;
use PDOStatement;


use BitBalm\Vinyl\V1 as Vinyl;
use BitBalm\Vinyl\V1\RecordStore\SQL\SQLImplementation;
use BitBalm\Vinyl\V1\Record as Record;
use BitBalm\Vinyl\V1\Collection as Collection;
use BitBalm\Vinyl\V1\Exception\RecordNotFound;


trait PDOImplementation /* implements Vinyl\RecordStore\SQL\PDO */
{ 
    use SQLImplementation;
    
    
    protected $pdo;


    public function __construct( 
        string $table_name, 
        string $primary_key_name, 
        PDO $pdo,
        array  $field_names = []
      )
    {
        $this->table_name       = $table_name;
        $this->primary_key_name = $primary_key_name;
        $this->pdo              = $connection->getPdo();
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

    
    public function getRecords( array $record_ids ) : Collection\Records 
    {
        return $this->getRecordsByFieldValues( $this->getPrimaryKey(), $record_ids );
    }
    
    public function getRecordByFieldValue( string $field, $value ) : Record 
    {
        $records = $this->getRecordsByFieldValues( $field, [ $value ] );
        $this->hasOnlyOneRecord($records);
        return current($records);
    }
    
    
    /* completes implementation of Vinyl\RecordStore\SQL\PDO */
    
    public function getRecordsByQueryString( string $query, array $parameters ) : Collection\Records 
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
        $this->hasOnlyOneRecord($records);
        return current($records);
    }
    
    public function getRecordsByStatement( PDOStatement $statement, array $parameters = [] ) : Collection\Records 
    {
        // Some statements may already be executed - execute it ourselves only if provided parameters, or if it hasn't been executed.
        if ( !empty($parameters) or $statement->columnCount() == 0 ) { 
            $statement->execute($parameters); 
        }
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC); #TODO: generator?
        
        return $this->getRecordsFromRows($rows);
    }
    
    protected function getRecordsFromRows( array $rows ) : Collection\Records
    {
        $records = clone $this->records;
        foreach( $rows as $row ) {
            $record = clone $this->record;
            $record->initializeRecord( $row[$this->getPrimaryKey()], $row );
            $records[] = $record;
        }
        
        return $records;
    }
    
}
