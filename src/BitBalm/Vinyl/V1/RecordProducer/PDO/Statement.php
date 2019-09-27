<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordProducer\PDO;

use Countable;
use IteratorIterator;
use PDO;
use PDOStatement;


use BitBalm\Vinyl\V1 as Vinyl;


class Statement extends IteratorIterator implements Vinyl\RecordProducer\PDO, Countable
{
    protected /*PDOStatement*/ $statement;
    protected /*Vinyl\Record*/ $record;
    protected /*string*/ $id_field;
    
    
    public function __construct( Vinyl\Record $prototype, string $id_field = 'id' )
    {
        $this->record     = $prototype;
        $this->id_field   = $id_field;
    }
    
    protected function setStatement( PDOStatement $statement )
    {
        $this->statement = $statement;
        $statement->setFetchMode(PDO::FETCH_ASSOC);
        parent::__construct( $statement );
        $this->rewind();
    }
    
    public function withStatement( 
        PDOStatement $statement, 
        Vinyl\Record $prototype = null, 
        string $id_field = null 
      ) : Vinyl\RecordProducer\PDO
    {
        $producer = new self( $prototype ?: $this->record, $id_field ?: $this->id_field);
        $producer->setStatement($statement);
        return $producer;
    }
    
    public function getMasterRecord() : Vinyl\Record
    {
        return $this->record;
    }
    
    public function current() : Vinyl\Record 
    {
        $row = parent::current();
        $record = $this->record->withValues( $row[$this->id_field], $row );
        return $record;
    }
    
    public function count() 
    {
        return $this->statement->rowCount();
    }
    
}
