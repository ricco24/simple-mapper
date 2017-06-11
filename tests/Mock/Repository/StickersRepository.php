<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Repository;

use SimpleMapper\Repository;

class StickersRepository extends Repository
{
    /** @var string */
    protected static $tableName = 'stickers';
}