<?php

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class EmptyStructure implements Structure
{
    /**
     * Register new active row class for table
     * @param string $tableName
     * @param string $activeRowClass
     * @return Structure
     */
    public function registerActiveRowClass(string $tableName, string $activeRowClass): Structure
    {
        return $this;
    }

    /**
     * Register new selection class for table
     * @param string $tableName
     * @param string $selectionClass
     * @return Structure
     */
    public function registerSelectionClass(string $tableName, string $selectionClass): Structure
    {
        return $this;
    }

    /**
     * Register new scopes for table
     * @param string $tableName
     * @param array $scopes
     * @return Structure
     */
    public function registerScopes(string $tableName, array $scopes): Structure
    {
        return $this;
    }

    /**
     * Fetch row class by table
     * @param string $table
     * @return string
     */
    public function getActiveRowClass(string $table): string
    {
        return ActiveRow::class;
    }

    /**
     * Fetch selection class by table
     * @param string $table
     * @return string
     */
    public function getSelectionClass(string $table): string
    {
        return Selection::class;
    }

    /**
     * Returns all scopes registered for table
     * @param string $table
     * @return array
     */
    public function getScopes(string $table): array
    {
        return [];
    }

    /**
     * Returns one scope
     * @param string $table
     * @param string $scope
     * @return Scope|null
     */
    public function getScope(string $table, string $scope): ?Scope
    {
        return null;
    }
}
