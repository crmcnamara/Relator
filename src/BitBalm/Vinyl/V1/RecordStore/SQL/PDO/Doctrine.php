<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;


use BitBalm\Vinyl\V1 as Vinyl;


class Doctrine implements Vinyl\RecordStore
{
    use Doctrine\Implementation;
}
