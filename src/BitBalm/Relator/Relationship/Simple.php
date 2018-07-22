<?php 

namespace BitBalm\Relator\Relationship;

use BitBalm\Relator\Relationship;
use BitBalm\Relator\Mappable;
use BitBalm\Relator\Relatable;



class Simple implements Relationship
{
    
    protected $fromTable ;
    protected $fromColumn ;
    protected $toTable ;
    protected $toColumn ;
    
    public function __construct( Mappable $fromTable, string $fromColumn, Relatable $toTable, string $toColumn ) 
    {
        foreach ( [ 'fromTable', 'fromColumn', 'toTable', 'toColumn', ] as $var ) {
            if ( empty( $$var ) ) {
                throw new InvalidArgumentExcpetion( "Invalid argument for $var" );
            }
            $this->$var = $$var ;
        }
    }
    
    public function getFromTable()  : Mappable  { return $this->fromTable   ; }
    public function getFromColumn() : string    { return $this->fromColumn  ; }
    public function getToTable()    : Relatable { return $this->toTable     ; }
    public function getToColumn()   : string    { return $this->toColumn    ; }
    
}
