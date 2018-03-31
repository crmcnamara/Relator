<?php 

namespace BitBalm\Relator\Relationship;

use BitBalm\Relator\Relationship;
use BitBalm\Relator\Record;



class Simple implements Relationship
{
    
    protected $fromTable ;
    protected $fromColumn ;
    protected $toTable ;
    protected $toColumn ;
    
    public function __construct( Record $fromTable, string $fromColumn, Record $toTable, string $toColumn ) 
    {
        foreach ( [ 'fromTable', 'fromColumn', 'toTable', 'toColumn', ] as $var ) {
            if ( empty( $$var ) ) {
                throw new InvalidArgumentExcpetion( "Invalid argument for $var" );
            }
            $this->$var = $$var ;
        }
    }
    
    public function getFromTable()  : Record  { return $this->fromTable   ; }
    public function getFromColumn() : string  { return $this->fromColumn  ; }
    public function getToTable()    : Record  { return $this->toTable     ; }
    public function getToColumn()   : string  { return $this->toColumn    ; }
    
}