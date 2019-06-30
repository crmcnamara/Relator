<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\Record;


use BitBalm\Vinyl\Record;


interface Active extends Record
{
    public function saveRecord();
    public function removeRecord();
}
