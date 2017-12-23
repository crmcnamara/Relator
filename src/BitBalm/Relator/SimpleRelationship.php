<?php 

namespace BitBalm\Relator;

class SimpleRelationship implements Relationship
{
    
    protected $relator ;
    
    protected $fromTable ;
    protected $fromColumn ;
    protected $toTable ;
    protected $toColumn ;
    
    public function __construct( Record $fromTable, $fromColumn, Record $toTable, $toColumn ) 
    {
        foreach ( [ 'fromTable', 'fromColumn', 'toTable', 'toColumn', ] as $var ) {
            if ( empty( $$var ) ) {
                throw new InvalidArgumentExcpetion( "Invalid argument for $var" );
            }
            $this->$var = $$var ;
        }
    }
    
    public function getFromTable()  { return $this->fromTable   ; }
    public function getFromColumn() { return $this->fromColumn  ; }
    public function getToTable()    { return $this->toTable     ; }
    public function getToColumn()   { return $this->toColumn    ; }
    
    public function getRelator() : Relator
    {
        return $this->relator;
    }
    
    public function setRelator( Relator $relator )
    {
        $this->relator = $relator;
        return $this;
    }
    
    
}