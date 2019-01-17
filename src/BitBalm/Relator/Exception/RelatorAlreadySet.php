<?php

namespace BitBalm\Relator\Exception;

use InvalidArgumentException;


class RelatorAlreadySet extends InvalidArgumentException implements AlreadySetException {}
