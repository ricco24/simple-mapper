<?php

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class EmptyStructure implements Structure
{
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
