<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class EmptyStructure implements Structure
{
    public function registerActiveRowClass(string $tableName, string $activeRowClass): Structure
    {
        return $this;
    }

    public function registerSelectionClass(string $tableName, string $selectionClass): Structure
    {
        return $this;
    }

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

    public function getScopes(string $tableName): array
    {
        return [];
    }

    public function getScope(string $tableName, string $scope): ?Scope
    {
        return null;
    }
}
