<?php 

namespace BitBalm\Relator;

class SimpleRelationship implements Relationship
{
    
    protected $relator ;
    
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
    
    public function getRelator() : Relator
    {
        return $this->relator;
    }
    
    public function setRelator( Relator $relator ) : Relationship
    {
        $this->relator = $relator;
        return $this;
    }
    
    
}
