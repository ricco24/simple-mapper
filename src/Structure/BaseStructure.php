<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class BaseStructure implements Structure
{
    /** @var array */
    protected $data = [];

    /**
     * Register new active row class for table
     * @param string $tableName
     * @param string $activeRowClass
     * @return Structure|BaseStructure
     */
    public function registerActiveRowClass(string $tableName, string $activeRowClass): Structure
    {
        $this->data[$tableName]['row'] = $activeRowClass;
        return $this;
    }

    /**
     * Register new selection class for table
     * @param string $tableName
     * @param string $selectionClass
     * @return Structure|BaseStructure
     */
    public function registerSelectionClass(string $tableName, string $selectionClass): Structure
    {
        $this->data[$tableName]['selection'] = $selectionClass;
        return $this;
    }

    /**
     * Register new scopes for table
     * @param string $tableName
     * @param array $scopes
     * @return Structure|BaseStructure
     * @throws SimpleMapperException
     */
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

    /**
     * Fetch row class by table
     * @param string $tableName
     * @return string
     */
    public function getActiveRowClass(string $tableName): string
    {
        return $this->data[$tableName]['row'] ?? ActiveRow::class;
    }

    /**
     * Fetch selection class by table
     * @param string $tableName
     * @return string
     */
    public function getSelectionClass(string $tableName): string
    {
        return $this->data[$tableName]['selection'] ?? Selection::class;
    }

    /**
     * Returns all scopes registered for table
     * @param string $tableName
     * @return array
     */
    public function getScopes(string $tableName): array
    {
        return $this->data[$tableName]['scopes'] ?? [];
    }

    /**
     * Returns one scope
     * @param string $tableName
     * @param string $scope
     * @return Scope|null
     */
    public function getScope(string $tableName, string $scope): ?Scope
    {
        return $this->data[$tableName]['scopes'][$scope] ?? null;
    }
}
