<?php 
declare (strict_types=1);

namespace BitBalm\Vinyl\V1\RecordStore\SQL\PDO;

use PDO;

use BitBalm\Vinyl\V1 as Vinyl;


class Atlas extends Vinyl\RecordStore\SQL\Generic implements Vinyl\RecordStore\SQL\PDO 
{
    use AtlasImplementation;
}
