<?php

namespace BitBalm\Relator\Tests\Mocks;

use BitBalm\Relator\Recordable;
use BitBalm\Relator\Mappable\MappableTrait;
use BitBalm\Relator\Recordable\RecordableTrait;


class RecordableArticle implements Recordable { use MappableTrait, RecordableTrait; }
