<?php

namespace BitBalm\Relator\Exception;

use InvalidArgumentException;


class RelationshipNotYetSet extends InvalidArgumentException implements NotYetSetException {}
