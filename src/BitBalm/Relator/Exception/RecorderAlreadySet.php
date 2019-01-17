<?php

namespace BitBalm\Relator\Exception;

use InvalidArgumentException;


class RecorderAlreadySet extends InvalidArgumentException implements AlreadySetException {}
