<?php

declare(strict_types=1);

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

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
     * @return class-string<ActiveRow>
     */
    public function getActiveRowClass(string $tableName): string;

    /**
     * Fetch selection class by table
     * @return class-string<Selection>
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
