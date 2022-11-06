<?php

declare(strict_types=1);

namespace SimpleMapper;

use Exception;
use Nette\Database\DriverException;
use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use PDOException;
use SimpleMapper\Behaviour\Behaviour;
use SimpleMapper\Exception\RepositoryException;
use SimpleMapper\Structure\EmptyStructure;
use SimpleMapper\Structure\Structure;

/**
 * Base repository class
 */
abstract class Repository
{
    protected Explorer $databaseExplorer;

    protected ?Structure $structure;

    /** Soft delete field, if empty soft delete is disabled */
    protected string $softDelete = '';

    private array $behaviours = [];

    protected static string $tableName = 'unknown';

    public function __construct(Explorer $databaseExplorer)
    {
        $this->databaseExplorer = $databaseExplorer;
        $this->structure = new EmptyStructure();
        $this->configure();
    }

    public function setStructure(Structure $structure): void
    {
        $this->structure = $structure;
        if (count($this->getScopes())) {
            $this->structure->registerScopes(static::getTableName(), $this->getScopes());
        }
    }

    public static function getTableName(): string
    {
        return static::$tableName;
    }

    /**
     * Prefix given string (column name) with table name
     */
    public static function prefixColumn(string $column): string
    {
        return static::getTableName() . '.' . $column;
    }

    public function getDatabaseExplorer(): Explorer
    {
        return $this->databaseExplorer;
    }

    /********************************************************************\
    | Magic methods
    \********************************************************************/

    /**
     * @throws RepositoryException
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'scope')) {
            $scopeName = lcfirst(substr($name, 5));
            $scope = $this->structure->getScope(static::$tableName, $scopeName);
            if (!$scope) {
                throw new RepositoryException('Scope ' . $scopeName . ' is not defined for table ' . static::$tableName);
            }

            $scopeNameToCall = 'scope' . ucfirst($scope->getName());
            return call_user_func_array([$this->findAll(), $scopeNameToCall], $arguments);
        }

        throw new RepositoryException('Call to undefined method ' . get_class($this) . '::' . $name . '()');
    }

    /********************************************************************\
    | Wrapper methods
    \********************************************************************/

    public function findAll(): Selection
    {
        return $this->prepareSelection($this->getTable());
    }

    public function findBy(array $by): Selection
    {
        return $this->prepareSelection($this->getTable()->where($by));
    }

    public function fetchPairs(string $key = null, string $value = null, string $order = null, array $where = []): array
    {
        $result = [];
        $pairs = $this->findBy($where);
        if ($order) {
            $pairs->order($order);
        }

        foreach ($pairs->fetchPairs($key, $value) as $k => $v) {
            $result[$k] = $v instanceof NetteDatabaseActiveRow ? $this->prepareRecord($v) : $v;
        }
        return $result;
    }

    /**
     * @throws Exception
     */
    public function insert(array $data): ?ActiveRow
    {
        $result = $this->transaction(function () use ($data) {
            foreach ($this->behaviours as $behaviour) {
                $data = $behaviour->beforeInsert($data);
            }

            $record = $this->getTable()->insert($data);
            if (!($record instanceof NetteDatabaseActiveRow)) {
                return null;
            }

            $record = $this->prepareRecord($record);

            foreach ($this->behaviours as $behaviour) {
                $behaviour->afterInsert($record, $data);
            }

            return $record;
        });

        return $result instanceof NetteDatabaseActiveRow ? $this->prepareRecord($result) : $result;
    }

    public function update(ActiveRow $record, array $data): ?ActiveRow
    {
        $result = $this->transaction(function () use ($record, $data) {
            $oldRecord = clone $record;

            foreach ($this->behaviours as $behaviour) {
                $data = $behaviour->beforeUpdate($record, $data);
            }

            $result = $record->update($data);

            foreach ($this->behaviours as $behaviour) {
                $behaviour->afterUpdate($oldRecord, $record, $data);
            }

            return $result ? $record : null;
        });

        return $result instanceof NetteDatabaseActiveRow ? $this->prepareRecord($result) : $result;
    }

    public function delete(ActiveRow $record): bool
    {
        return $this->transaction(function () use ($record): bool {
            $oldRecord = clone $record;

            foreach ($this->behaviours as $behaviour) {
                $behaviour->beforeDelete($record, (bool) $this->softDelete);
            }

            if ($this->softDelete) {
                $result = $record->update([
                    $this->softDelete => true
                ]);
            } else {
                $result = $record->delete();
            }

            foreach ($this->behaviours as $behaviour) {
                $behaviour->afterDelete($oldRecord, (bool) $this->softDelete);
            }

            return (bool) $result;
        });
    }

    /********************************************************************\
    | Internal methods
    \********************************************************************/

    protected function getTable(): NetteDatabaseSelection
    {
        return $this->databaseExplorer->table(static::getTableName());
    }

    protected function registerBehaviour(Behaviour $behaviour): Repository
    {
        $this->behaviours[get_class($behaviour)] = $behaviour;
        return $this;
    }

    protected function getBehaviour(string $class): ?Behaviour
    {
        return $this->behaviours[$class] ?? null;
    }

    /**
     * Configure repository
     */
    protected function configure(): void
    {
        // override in child
    }

    /**
     * Define table scopes
     * @return array
     */
    protected function getScopes(): array
    {
        // override in child
        return [];
    }

    /********************************************************************\
    | Builder methods
    \********************************************************************/

    private function prepareSelection(NetteDatabaseSelection $selection): Selection
    {
        $selectionClass = $this->structure->getSelectionClass($selection->getName());
        return new $selectionClass($selection, $this->structure);
    }

    private function prepareRecord(NetteDatabaseActiveRow $row): ActiveRow
    {
        $rowClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $rowClass($row, $this->structure);
    }

    /********************************************************************\
    | Helper methods
    \********************************************************************/

    /**
     * Run new transaction if no transaction is running, do nothing otherwise
     */
    public function transaction(callable $callback): mixed
    {
        try {
            // Check if transaction already running
            $inTransaction = $this->getDatabaseExplorer()->getConnection()->getPdo()->inTransaction();
            if (!$inTransaction) {
                $this->getDatabaseExplorer()->beginTransaction();
            }

            $result = $callback($this);

            if (!$inTransaction) {
                $this->getDatabaseExplorer()->commit();
            }
        } catch (Exception $e) {
            if (isset($inTransaction) && !$inTransaction && $e instanceof PDOException) {
                $this->getDatabaseExplorer()->rollBack();
            }
            throw $e;
        }

        return $result;
    }

    /**
     * @throws DriverException
     */
    public function ensure(callable $callback, int $retryTimes = 1): mixed
    {
        try {
            return $callback($this);
        } catch (DriverException $e) {
            if ($retryTimes == 0) {
                throw $e;
            }
            $this->getDatabaseExplorer()->getConnection()->reconnect();
            return $this->ensure($callback, $retryTimes - 1);
        }
    }

    /**
     * Try call callback X times
     * @throws DriverException
     */
    public function retry(callable $callback, int $retryTimes = 3): mixed
    {
        try {
            return $callback($this);
        } catch (DriverException $e) {
            if ($retryTimes == 0) {
                throw $e;
            }
            return $this->retry($callback, $retryTimes - 1);
        }
    }

    /**
     * Paginate callback
     */
    public function chunk(Selection $selection, int $limit, callable $callback): void
    {
        $count = $selection->count('*');
        $pages = ceil($count / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $callback($selection->page($i + 1, $limit));
        }
    }
}
