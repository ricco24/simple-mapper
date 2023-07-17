<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class EmptyStructure implements Structure
{
    /**
     * @param Scope[] $scopes
     */
    public function registerScopes(string $tableName, array $scopes): Structure
    {
        return $this;
    }

    public function getActiveRowClass(string $tableName): string
    {
        return ActiveRow::class;
    }

    public function getSelectionClass(string $tableName): string
    {
        return Selection::class;
    }

    /**
     * @return Scope[]
     */
    public function getScopes(string $tableName): array
    {
        return [];
    }

    public function getScope(string $tableName, string $scope): ?Scope
    {
        return null;
    }
}
