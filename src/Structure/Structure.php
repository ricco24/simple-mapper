<?php

namespace SimpleMapper\Structure;

use SimpleMapper\Scope\Scope;

interface Structure
{
    /**
     * Fetch row class by table
     * @param string $table
     * @return string
     */
    public function getActiveRowClass(string $table): string;

    /**
     * Fetch selection class by table
     * @param string $table
     * @return string
     */
    public function getSelectionClass(string $table): string;

    /**
     * Returns all scopes registered for table
     * @param string $table
     * @return array
     */
    public function getScopes(string $table): array;

    /**
     * Returns one scope
     * @param string $table
     * @param string $scope
     * @return Scope|null
     */
    public function getScope(string $table, string $scope): ?Scope;
}
