<?php

namespace SimpleMapper;

use Nette\Database\Context;
use Nette\Database\DriverException;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use SimpleMapper\Behaviour\Behaviour;
use SimpleMapper\Structure\Structure;
use Traversable;
use Exception;
use PDOException;

/**
 * Base repository class
 */
abstract class Repository
{
    /** @var Context */
    protected $databaseContext;

    /** @var Structure|null */
    protected $structure;

    /** @var string             Soft delete field, if empty soft delete is disabled */
    protected $softDelete = '';

    /** @var array */
    private $behaviours = [];

    /** @var string */
    protected static $tableName = 'unknown';

    /**
     * @param Context $databaseContext
     * @param Structure $structure
     */
    public function __construct(Context $databaseContext, Structure $structure)
    {
        $this->databaseContext = $databaseContext;
        $this->structure = $structure;

        if (count($this->getScopes())) {
            $this->structure->registerScopes(static::$tableName, $this->getScopes());
        }

        $this->configure();
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return static::$tableName;
    }

    /**
     * @return NetteDatabaseSelection
     */
    public function getTable(): NetteDatabaseSelection
    {
        return $this->databaseContext->table(static::getTableName());
    }

    /**
     * @return Context
     */
    public function getDatabaseContext(): Context
    {
        return $this->databaseContext;
    }

    /**
     * @param Behaviour $behaviour
     * @return Repository
     */
    public function registerBehaviour(Behaviour $behaviour): Repository
    {
        $this->behaviours[get_class($behaviour)] = $behaviour;
        return $this;
    }

    /**
     * Get behaviour by class
     * @param string $class
     * @return Behaviour|null
     */
    public function getBehaviour($class): ?Behaviour
    {
        return $this->behaviours[$class] ?? null;
    }

    /**
     * Configure repository
     */
    public function configure(): void
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
    | Magic methods
    \********************************************************************/

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (substr($name, 0, 5) === 'scope') {
            $scopeName = lcfirst(substr($name, 5));
            $scope = $this->structure->getScope(static::$tableName, $scopeName);
            if (!$scope) {
                trigger_error('Scope ' . $scopeName . ' is not defined for table ' . static::$tableName, E_USER_ERROR);
            }

            $scopeNameToCall = 'scope' . ucfirst($scope->getName());
            return call_user_func_array([$this->findAll(), $scopeNameToCall], $arguments);
        }

        trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
    }

    /********************************************************************\
    | Wrapper methods
    \********************************************************************/

    /**
     * Find all records
     * @return Selection
     */
    public function findAll(): Selection
    {
        return $this->prepareSelection($this->getTable());
    }

    /**
     * Find by conditions
     * @param array $by
     * @return Selection
     */
    public function findBy(array $by): Selection
    {
        return $this->prepareSelection($this->getTable()->where($by));
    }

    /**
     * Returns all rows as associative array
     * @param string|null $key
     * @param string|null $value
     * @param string|null $order
     * @param array $where
     * @return array
     */
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
     * Insert one record
     * @param array|Traversable $data
     * @return ActiveRow|null
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

    /**
     * Update one record
     * @param ActiveRow $record
     * @param array $data
     * @return ActiveRow|null
     */
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

    /**
     * Delete one record
     * @param ActiveRow $record
     * @return bool
     */
    public function delete(ActiveRow $record): bool
    {
        $result = $this->transaction(function () use ($record): bool {
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

        return $result;
    }

    /********************************************************************\
    | Builder methods
    \********************************************************************/

    /**
     * @param NetteDatabaseSelection $selection
     * @return Selection
     */
    private function prepareSelection(NetteDatabaseSelection $selection): Selection
    {
        $selectionClass = $this->structure->getSelectionClass($selection->getName());
        return new $selectionClass($selection, $this->structure);
    }

    /**
     * @param NetteDatabaseActiveRow $row
     * @return ActiveRow
     */
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
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        try {
            // Check if transaction already running
            $inTransaction = $this->getDatabaseContext()->getConnection()->getPdo()->inTransaction();
            if (!$inTransaction) {
                $this->getDatabaseContext()->beginTransaction();
            }

            $result = $callback($this);

            if (!$inTransaction) {
                $this->getDatabaseContext()->commit();
            }
        } catch (Exception $e) {
            if (isset($inTransaction) && !$inTransaction && $e instanceof PDOException) {
                $this->getDatabaseContext()->rollBack();
            }
            throw $e;
        }

        return $result;
    }

    /**
     * @param callable $callback
     * @param int $retryTimes
     * @return mixed
     * @throws DriverException
     */
    public function ensure(callable $callback, int $retryTimes = 1)
    {
        try {
            return $callback($this);
        } catch (DriverException $e) {
            if ($retryTimes == 0) {
                throw $e;
            }
            $this->getDatabaseContext()->getConnection()->reconnect();
            return $this->ensure($callback, $retryTimes - 1);
        }
    }

    /**
     * Try call callback X times
     * @param callable $callback
     * @param int $retryTimes
     * @return mixed
     * @throws DriverException
     */
    public function retry(callable $callback, int $retryTimes = 3)
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
     * @param Selection $selection
     * @param int $limit
     * @param callable $callback
     */
    public function chunk(Selection $selection, int $limit, callable $callback)
    {
        $count = $selection->count('*');
        $pages = ceil($count / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $callback($selection->page($i + 1, $limit));
        }
    }
}
