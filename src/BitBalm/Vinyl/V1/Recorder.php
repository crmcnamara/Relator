<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1;


interface Recorder 
{
    public function getRow(               $type, $row_id                      ) : array ;
    public function getRows(              $type, array $row_ids               ) : Collection\Arrays ;
    public function getRowByFieldValues(  $type, string $field, $value        ) : Collection\Arrays ;
    public function getRowsByFieldValues( $type, string $field, array $values ) : Collection\Arrays ;
    
    public function insertRow(            $type,          array $values       ) : array ; 
    public function updateRow(            $type, $row_id, array $values       ) : array ;
    public function deleteRow(            $type, $row_id                      );
}
