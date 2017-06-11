<?php

declare(strict_types=1);

namespace SimpleMapper;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use Nette\InvalidArgumentException;
use ArrayAccess;
use Iterator;
use Countable;
use SimpleMapper\Structure\Structure;
use Traversable;

class Selection implements Iterator, Countable, ArrayAccess
{
    /** @var NetteDatabaseSelection */
    private $selection;

    /** @var Structure */
    protected $structure;

    /**
     * @param NetteDatabaseSelection $selection
     * @param Structure $structure
     */
    public function __construct(NetteDatabaseSelection $selection, Structure $structure)
    {
        $this->selection = $selection;
        $this->structure = $structure;
    }

    /**
     * @return NetteDatabaseSelection
     */
    public function getSelection(): NetteDatabaseSelection
    {
        return $this->selection;
    }

    /********************************************************************\
    | Magic methods
    \********************************************************************/

    /**
     * Clone object
     */
    public function __clone()
    {
        $this->selection = clone $this->selection;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (substr($name, 0, 5) === 'scope') {
            $scopeName = lcfirst(substr($name, 5));
            $scope = $this->structure->getScope($this->selection->getName(), $scopeName);
            if (!$scope) {
                trigger_error('Scope ' . $scopeName . ' is not defined for table ' . $this->selection->getName(), E_USER_ERROR);
            }
            return $this->where(call_user_func_array($scope->getCallback(), $arguments));
        }

        trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
    }

    /**********************************************************************\
     * Wrapper function - fetch
    \**********************************************************************/

    /**
     * Returns row specified by primary key
     * @param mixed $key Primary key
     * @return ActiveRow|null
     */
    public function get($key): ?ActiveRow
    {
        $row = $this->selection->get($key);
        return $row instanceof NetteDatabaseActiveRow ? $this->prepareRecord($row) : null;
    }

    /**
     * Returns one record
     * @return ActiveRow|null
     */
    public function fetch(): ?ActiveRow
    {
        $row = $this->selection->fetch();
        return $row ? $this->prepareRecord($row) : null;
    }

    /**
     * Fetches single field
     * @param string|null $column
     * @return mixed
     */
    public function fetchField(string $column = null)
    {
        return $this->selection->fetchField($column);
    }

    /**
     * Fetch key => value pairs
     * @param mixed $key
     * @param mixed $value
     * @return array
     */
    public function fetchPairs($key = null, $value = null): array
    {
        $result = [];

        $pairs = $this->selection->fetchPairs($key, $value);
        foreach ($pairs as $k => $v) {
            $result[$k] = $v instanceof NetteDatabaseActiveRow ? $this->prepareRecord($v) : $v;
        }
        return $result;
    }

    /**
     * Returns all records
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->prepareRecords($this->selection->fetchAll());
    }

    /**
     * Some examples of usage: https://github.com/nette/utils/blob/master/tests%2FUtils%2FArrays.associate().phpt
     * @param mixed $path
     * @return array|\stdClass
     */
    public function fetchAssoc($path)
    {
        return $this->selection->fetchAssoc($path);
    }

    /**********************************************************************\
     * Wrapper function - sql selections
    \**********************************************************************/

    /**
     * Adds select clause, more calls appends to the end
     * @param string $columns   for example "column, MD5(column) AS column_md5"
     * @param mixed ...$params
     * @return Selection
     */
    public function select($columns, ...$params): Selection
    {
        $this->selection->select($columns, ...$params);
        return $this;
    }

    /**
     * Adds condition for primary key
     * @param mixed $key
     * @return Selection
     */
    public function wherePrimary($key): Selection
    {
        $this->selection->wherePrimary($key);
        return $this;
    }

    /**
     * Adds where condition, more calls appends with AND
     * @param string|string[] $condition
     * @param mixed ...$params
     * @return Selection
     */
    public function where($condition, ...$params): Selection
    {
        $this->selection->where($condition, ...$params);
        return $this;
    }

    /**
     * Adds ON condition when joining specified table, more calls appends with AND
     * @param string $tableChain    table chain or table alias for which you need additional left join condition
     * @param string|string[] $condition     condition possibly containing ?
     * @param mixed ...$params
     * @return Selection
     */
    public function joinWhere(string $tableChain, $condition, ...$params): Selection
    {
        $this->selection->joinWhere($tableChain, $condition, ...$params);
        return $this;
    }

    /**
     * Adds where condition using the OR operator between parameters
     * More calls appends with AND.
     * @param array $parameters     ['column1' => 1, 'column2 > ?' => 2, 'full condition']
     * @return Selection
     * @throws InvalidArgumentException
     */
    public function whereOr(array $parameters): Selection
    {
        $this->selection->whereOr($parameters);
        return $this;
    }

    /**
     * Adds order clause, more calls appends to the end
     * @param string $columns       for example 'column1, column2 DESC'
     * @param mixed ...$params
     * @return Selection
     */
    public function order(string $columns, ...$params): Selection
    {
        $this->selection->order($columns, ...$params);
        return $this;
    }

    /**
     * Sets limit clause, more calls rewrite old values
     * @param int $limit
     * @param int $offset
     * @return Selection
     */
    public function limit(int $limit, int $offset = null): Selection
    {
        $this->selection->limit($limit, $offset);
        return $this;
    }

    /**
     * Sets offset using page number, more calls rewrite old values
     * @param int $page
     * @param int $itemsPerPage
     * @param int|null $numOfPages
     * @return Selection
     */
    public function page(int $page, int $itemsPerPage, int & $numOfPages = null): Selection
    {
        $this->selection->page($page, $itemsPerPage, $numOfPages);
        return $this;
    }

    /**
     * Sets group clause, more calls rewrite old value
     * @param string $columns
     * @param mixed ...$params
     * @return Selection
     */
    public function group(string $columns, ...$params): Selection
    {
        $this->selection->group($columns, ...$params);
        return $this;
    }

    /**
     * Sets having clause, more calls rewrite old value
     * @param string $having
     * @param mixed ...$params
     * @return Selection
     */
    public function having(string $having, ...$params): Selection
    {
        $this->selection->having($having, ...$params);
        return $this;
    }

    /**
     * Aliases table. Example ':book:book_tag.tag', 'tg'
     * @param string $tableChain
     * @param string $alias
     * @return Selection
     */
    public function alias(string $tableChain, string $alias): Selection
    {
        $this->selection->alias($tableChain, $alias);
        return $this;
    }

    /**********************************************************************\
     * Wrapper function - aggregations
    \**********************************************************************/

    /**
     * Executes aggregation function
     * @param string $function Select call in "FUNCTION(column)" format
     * @return float
     */
    public function aggregation(string $function): float
    {
        return (float) $this->selection->aggregation($function);
    }

    /**
     * Counts number of rows
     * Countable interface
     * @param string $column If it is not provided returns count of result rows, otherwise runs new sql counting query
     * @return int
     */
    public function count(string $column = null): int
    {
        return (int) $this->selection->count($column);
    }

    /**
     * Returns minimum value from a column
     * @param string $column
     * @return float
     */
    public function min(string $column): float
    {
        return (float) $this->selection->min($column);
    }

    /**
     * Returns maximum value from a column
     * @param string $column
     * @return float
     */
    public function max(string $column): float
    {
        return (float) $this->selection->max($column);
    }

    /**
     * Returns sum of values in a column
     * @param string $column
     * @return float
     */
    public function sum(string $column): float
    {
        return (float) $this->selection->sum($column);
    }

    /**********************************************************************\
     * Wrapper function - manipulation
    \**********************************************************************/

    /**
     * Inserts row in a table
     * @param  array|Traversable|Selection $data
     * @return IRow|int|bool
     */
    public function insert($data)
    {
        $insertResult = $this->selection->insert($data);
        return $insertResult instanceof IRow ? $this->prepareRecord($insertResult) : $insertResult;
    }

    /**
     * Updates all rows in result set
     * @param  array|Traversable $data ($column => $value)
     * @return int
     */
    public function update($data): int
    {
        return $this->selection->update($data);
    }

    /**
     * Deletes all rows in result set
     * @return int
     */
    public function delete(): int
    {
        return $this->selection->delete();
    }

    /**********************************************************************\
     * Iterator interface
    \**********************************************************************/

    /**
     * Rewind selection
     */
    public function rewind(): void
    {
        $this->selection->rewind();
    }

    /**
     * Returns current selection data record
     * @return ActiveRow|null
     */
    public function current(): ?ActiveRow
    {
        $row = $this->selection->current();
        return $row instanceof IRow ? $this->prepareRecord($row) : null;
    }

    /**
     * Returns current selection data key
     * @return string|int Row ID
     */
    public function key()
    {
        return $this->selection->key();
    }

    /**
     * Move iterator
     */
    public function next(): void
    {
        $this->selection->next();
    }

    /**
     * It is selection valid
     * @return bool
     */
    public function valid(): bool
    {
        return $this->selection->valid();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    /**
     * @param string $key Row ID
     * @param IRow $value
     */
    public function offsetSet($key, $value): void
    {
        $this->selection->offsetSet($key, $value);
    }

    /**
     * Returns specified row
     * @param string $key Row ID
     * @return ActiveRow|null
     */
    public function offsetGet($key): ?ActiveRow
    {
        $row = $this->selection->offsetGet($key);
        return $row instanceof IRow ? $this->prepareRecord($row) : null;
    }

    /**
     * Tests if row exists
     * @param string $key Row ID
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return $this->selection->offsetExists($key);
    }

    /**
     * Removes row from result set
     * @param string $key Row ID
     */
    public function offsetUnset($key): void
    {
        $this->selection->offsetUnset($key);
    }

    /**********************************************************************\
     * Build methods
    \**********************************************************************/

    /**
     * Prepare one record
     * @param IRow $row
     * @return ActiveRow
     */
    protected function prepareRecord(IRow $row): ActiveRow
    {
        $recordClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $recordClass($row, $this->structure);
    }

    /**
     * Prepare records array
     * @param array $rows
     * @return array
     */
    protected function prepareRecords(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->prepareRecord($row);
        }
        return $result;
    }
}
