<?php

namespace SimpleMapper\Structure;

use SimpleMapper\ActiveRow;
use SimpleMapper\Exception\SimpleMapperException;
use SimpleMapper\Repository;
use SimpleMapper\Scope\Scope;
use SimpleMapper\Selection;

class CustomStructure implements Structure
{
    /** @var array */
    protected $data = [];

    /**
     * @param string $table
     * @param string|null $activeRowClass
     * @param string|null $selectionClass
     * @param Repository|null $repository
     * @return CustomStructure
     * @throws SimpleMapperException
     */
    public function registerTable(string $table, string $activeRowClass = null, string $selectionClass = null, Repository $repository = null): CustomStructure
    {
        if ($activeRowClass) {
            $this->data[$table]['row'] = $activeRowClass;
        }

        if ($selectionClass) {
            $this->data[$table]['selection'] = $selectionClass;
        }

        if ($repository) {
            foreach ($repository->getScopes() as $scope) {
                if (!($scope instanceof Scope)) {
                    throw new SimpleMapperException('Scopes can be only of class ' . Scope::class);
                }

                $this->data[$table]['scopes'][$scope->getName()] = $scope;
            }
            $repository->setStructure($this);
        }

        return $this;
    }

    /**
     * Fetch row class by table
     * @param string $table
     * @return string
     */
    public function getActiveRowClass(string $table): string
    {
        return isset($this->data[$table]['row'])
            ? $this->data[$table]['row']
            : ActiveRow::class;
    }

    /**
     * Fetch selection class by table
     * @param string $table
     * @return string
     */
    public function getSelectionClass(string $table): string
    {
        return isset($this->data[$table]['selection'])
            ? $this->data[$table]['selection']
            : Selection::class;
    }

    /**
     * Returns all scopes registered for table
     * @param string $table
     * @return array
     */
    public function getScopes(string $table): array
    {
        return isset($this->data[$table]['scopes'])
            ? $this->data[$table]['scopes']
            : [];
    }

    /**
     * Returns one scope
     * @param string $table
     * @param string $scope
     * @return Scope|null
     */
    public function getScope(string $table, string $scope): ?Scope
    {
        return isset($this->data[$table]['scopes'][$scope])
            ? $this->data[$table]['scopes'][$scope]
            : null;
    }
}
