<?php

declare(strict_types=1);

namespace SimpleMapper;

use SimpleMapper\Structure\Structure;

class Mapper
{
    private Structure $structure;

    private array $repositories = [];

    /**
     * @param Structure $structure
     */
    public function __construct(Structure $structure)
    {
        $this->structure = $structure;
    }

    /**
     * Map classes and scopes by repository
     */
    public function mapRepository(Repository $repository, ?string $activeRowClass = null, ?string $selectionClass = null): Mapper
    {
        $this->repositories[get_class($repository)] = $repository;
        $repository->setStructure($this->structure);

        if ($activeRowClass) {
            $this->structure->registerActiveRowClass($repository::getTableName(), $activeRowClass);
        }

        if ($selectionClass) {
            $this->structure->registerSelectionClass($repository::getTableName(), $selectionClass);
        }

        return $this;
    }

    /**
     * Map classes only by table name
     */
    public function mapTableName(string $tableName, string $activeRowClass = null, string $selectionClass = null): Mapper
    {
        if ($activeRowClass) {
            $this->structure->registerActiveRowClass($tableName, $activeRowClass);
        }

        if ($selectionClass) {
            $this->structure->registerSelectionClass($tableName, $selectionClass);
        }

        return $this;
    }

    public function getRepository(string $class): ?Repository
    {
        return $this->repositories[$class] ?? null;
    }
}
