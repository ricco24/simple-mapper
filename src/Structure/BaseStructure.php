<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class BaseStructure implements Structure
{
    protected array $data = [];

    public function registerActiveRowClass(string $tableName, string $activeRowClass): Structure
    {
        $this->data[$tableName]['row'] = $activeRowClass;
        return $this;
    }

    public function registerSelectionClass(string $tableName, string $selectionClass): Structure
    {
        $this->data[$tableName]['selection'] = $selectionClass;
        return $this;
    }

    public function registerScopes(string $tableName, array $scopes): Structure
    {
        foreach ($scopes as $scope) {
            if (!($scope instanceof Scope)) {
                throw new SimpleMapperException('Scopes can be only of class ' . Scope::class);
            }

            $this->data[$tableName]['scopes'][$scope->getName()] = $scope;
        }
        return $this;
    }

    public function getActiveRowClass(string $tableName): string
    {
        return $this->data[$tableName]['row'] ?? ActiveRow::class;
    }

    public function getSelectionClass(string $tableName): string
    {
        return $this->data[$tableName]['selection'] ?? Selection::class;
    }

    public function getScopes(string $tableName): array
    {
        return $this->data[$tableName]['scopes'] ?? [];
    }

    public function getScope(string $tableName, string $scope): ?Scope
    {
        return $this->data[$tableName]['scopes'][$scope] ?? null;
    }
}
