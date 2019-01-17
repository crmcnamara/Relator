<?php

namespace BitBalm\Relator\Exception;

use InvalidArgumentException;


class TableNameAlreadySet extends InvalidArgumentException implements AlreadySetException {}
