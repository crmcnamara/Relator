<?php 

namespace BitBalm\Relator\PDO;

use InvalidArgumentException;
use BitBalm\Relator\BaseRelationship;

Class SimpleRelationship extends BaseRelationship implements Relationship 
{
    
    protected $fromTable ;
    protected $fromColumn ;
    protected $toTable ;
    protected $toColumn ;
    protected $fetchMode = [] ;
    
    
    public function __construct( $fromTable, $fromColumn, $toTable, $toColumn, array $fetchMode = null ) 
    {
        foreach ( [ 'fromTable', 'fromColumn', 'toTable', 'toColumn', ] as $var ) {
            if ( empty( $$var = stringval( $$var ) ) ) {
                throw new InvalidArgumentExcpetion( "Invalid argument for $var" );
            }
            $this->$var = $$var ;
        }
        
        $this->fetchMode = (array) $fetchMode ;
        
    }
    
    public function getFromTable()  { return $this->fromTable   ; }
    public function getFromColumn() { return $this->fromColumn  ; }
    public function getToTable()    { return $this->toTable     ; }
    public function getToColumn()   { return $this->toColumn    ; }
    public function getFetchMode()  { return $this->fetchMode   ; }
    
}
