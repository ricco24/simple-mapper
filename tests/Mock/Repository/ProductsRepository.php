<?php

namespace Kelemen\SimpleMapper\Tests\Mock\Repository;

use Kelemen\SimpleMapper\Tests\Mock\Selection\Products;
use SimpleMapper\Behaviour\DateBehaviour;
use SimpleMapper\Repository;
use SimpleMapper\Scope\Scope;

/**
 * @method Products scopeAdmin()
 * @method Products scopePriceGreater(float $price = 20)
 */
class ProductsRepository extends Repository
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
            }),
            new Scope('priceGreater', function ($price = 20) {
                return [
                    self::getTableName() . '.price > ?' => $price
                ];
            })
        ];
    }
}