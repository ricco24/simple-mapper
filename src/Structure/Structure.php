<?php

namespace SimpleMapper\Structure;

use SimpleMapper\Scope\Scope;

interface Structure
{
    /**
     * Register new active row class for table
     * @param string $tableName
     * @param string $activeRowClass
     * @return Structure
     */
    public function registerActiveRowClass(string $tableName, string $activeRowClass): Structure;

    /**
     * Register new selection class for table
     * @param string $tableName
     * @param string $selectionClass
     * @return Structure
     */
    public function registerSelectionClass(string $tableName, string $selectionClass): Structure;

    /**
     * Register new scopes for table
     * @param string $tableName
     * @param array $scopes
     * @return Structure
     */
    public function registerScopes(string $tableName, array $scopes): Structure;

    /**
     * Fetch row class by table
     * @param string $tableName
     * @return string
     */
    public function getActiveRowClass(string $tableName): string;

    /**
     * Fetch selection class by table
     * @param string $tableName
     * @return string
     */
    public function getSelectionClass(string $tableName): string;

    /**
     * Returns all scopes registered for table
     * @param string $tableName
     * @return array
     */
    public function getScopes(string $tableName): array;

    /**
     * Returns one scope
     * @param string $tableName
     * @param string $scope
     * @return Scope|null
     */
    public function getScope(string $tableName, string $scope): ?Scope;
}
