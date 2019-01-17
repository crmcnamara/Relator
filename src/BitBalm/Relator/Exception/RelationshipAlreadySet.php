<?php

namespace BitBalm\Relator\Exception;

use InvalidArgumentException;


class RelationshipAlreadySet extends InvalidArgumentException implements AlreadySetException {}
