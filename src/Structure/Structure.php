<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Scope\Scope;

interface Structure
{
    /**
     * Register new scopes for table
     * @param Scope[] $scopes
     * @throws SimpleMapperException
     */
    public function registerScopes(string $tableName, array $scopes): Structure;

    /**
     * Fetch row class by table
     */
    public function getActiveRowClass(string $tableName): string;

    /**
     * Fetch selection class by table
     */
    public function getSelectionClass(string $tableName): string;

    /**
     * Returns all scopes registered for table
     * @return Scope[]
     */
    public function getScopes(string $tableName): array;

    /**
     * Returns one scope
     */
    public function getScope(string $tableName, string $scope): ?Scope;
}
