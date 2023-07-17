<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class BaseStructure implements Structure
{
    /** @var array<string, string> */
    private array $activeRows = [];

    /** @var array<string, string> */
    private array $selections = [];

    /** @var array<string, array<string, Scope>> */
    private array $scopes = [];

    /**
     * @param class-string<ActiveRow>|null $activeRowClass
     * @param class-string<Selection>|null $selectionClass
     */
    public function registerMapping(string $tableName, ?string $activeRowClass, ?string $selectionClass): Structure
    {
        if ($activeRowClass !== null) {
            $this->activeRows[$tableName] = $activeRowClass;
        }
        if ($selectionClass !== null) {
            $this->selections[$tableName] = $selectionClass;
        }
        return $this;
    }

    /**
     * @param Scope[] $scopes
     * @throws SimpleMapperException
     */
    public function registerScopes(string $tableName, array $scopes): Structure
    {
        foreach ($scopes as $scope) {
            if (!($scope instanceof Scope)) {
                throw new SimpleMapperException('Scopes can be only of class ' . Scope::class);
            }

            $this->scopes[$tableName][$scope->getName()] = $scope;
        }
        return $this;
    }

    public function getActiveRowClass(string $tableName): string
    {
        return $this->activeRows[$tableName] ?? ActiveRow::class;
    }

    public function getSelectionClass(string $tableName): string
    {
        return $this->selections[$tableName] ?? Selection::class;
    }

    /**
     * @return Scope[]
     */
    public function getScopes(string $tableName): array
    {
        return $this->scopes[$tableName] ?? [];
    }

    public function getScope(string $tableName, string $scope): ?Scope
    {
        return $this->scopes[$tableName][$scope] ?? null;
    }
}
