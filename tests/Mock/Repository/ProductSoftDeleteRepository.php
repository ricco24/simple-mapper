<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Repository;

use SimpleMapper\Behaviour\DateBehaviour;
use SimpleMapper\Repository;
use SimpleMapper\Scope\Scope;

class ProductSoftDeleteRepository extends Repository
{
    /** @var string */
    protected static $tableName = 'products';

    /** @var string */
    protected $softDelete = 'is_deleted';

    /**
     * Configure repository
     */
    protected function configure(): void
    {
        $this->registerBehaviour(new DateBehaviour());
    }

    /**
     * Register scopes
     * @return array
     */
    public function getScopes(): array
    {
        return [
            new Scope('admin', function () {
                return [
                    self::getTableName() . '.is_deleted' => false
                ];
            })
        ];
    }
}