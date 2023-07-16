<?php

declare(strict_types=1);

namespace SimpleMapper;

use ArrayAccess;
use Countable;
use Iterator;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use Nette\Database\Table\IRowContainer;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\InvalidArgumentException;
use SimpleMapper\Structure\Structure;

class Selection implements Iterator, IRowContainer, ArrayAccess, Countable
{
    private NetteDatabaseSelection $selection;

    protected Structure $structure;

    public function __construct(NetteDatabaseSelection $selection, Structure $structure)
    {
        $this->selection = $selection;
        $this->structure = $structure;
    }

    public function getSelection(): NetteDatabaseSelection
    {
        return $this->selection;
    }

    public function getName(): string
    {
        return $this->selection->getName();
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
        if (str_starts_with($name, 'scope')) {
            $scopeName = lcfirst(substr($name, 5));
            $scope = $this->structure->getScope($this->selection->getName(), $scopeName);
            if (!$scope) {
                trigger_error(
                    'Scope ' . $scopeName . ' is not defined for table ' . $this->selection->getName(),
                    E_USER_ERROR
                );
            }
            return $this->where(call_user_func_array($scope->getCallback(), $arguments));
        }

        trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . '()', E_USER_ERROR);
    }

    /**********************************************************************\
     * Wrapper function - fetch
    \**********************************************************************/

    public function get(mixed $key): ?ActiveRow
    {
        $row = $this->selection->get($key);
        return $row instanceof NetteDatabaseActiveRow ? $this->prepareRecord($row) : null;
    }

    public function fetch(): ?ActiveRow
    {
        $row = $this->selection->fetch();
        return $row ? $this->prepareRecord($row) : null;
    }

    /**
     * @deprecated
     */
    public function fetchField(?string $column = null): mixed
    {
        return $this->selection->fetchField($column);
    }

    /**
     * Fetches all rows as associative array.
     * @param  string|int  $key  column name used for an array key or null for numeric index
     * @param  string|int  $value  column name used for an array value or null for the whole row
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
     * Fetches all rows.
     * @return NetteDatabaseActiveRow[]
     */
    public function fetchAll(): array
    {
        return $this->prepareRecords($this->selection->fetchAll());
    }

    /**
     * Fetches all rows and returns associative tree.
     * Some examples of usage: https://github.com/nette/utils/blob/master/tests%2FUtils%2FArrays.associate().phpt
     * @param  string  $path  associative descriptor
     */
    public function fetchAssoc(string $path): array
    {
        return $this->prepareRecords($this->selection->fetchAssoc($path));
    }

    /**********************************************************************\
     * Wrapper function - sql selections
    \**********************************************************************/

    /**
     * Adds select clause, more calls appends to the end.
     * @param  string|string[]  $columns  for example "column, MD5(column) AS column_md5"
     * @return static
     */
    public function select($columns, ...$params): Selection
    {
        $this->selection->select($columns, ...$params);
        return $this;
    }

    public function wherePrimary(mixed $key): Selection
    {
        $this->selection->wherePrimary($key);
        return $this;
    }

    /**
     * Adds where condition, more calls appends with AND.
     * @param  string|array  $condition  possibly containing ?
     */
    public function where($condition, ...$params): Selection
    {
        $this->selection->where($condition, ...$params);
        return $this;
    }

    /**
     * Adds ON condition when joining specified table, more calls appends with AND.
     * @param  string  $tableChain  table chain or table alias for which you need additional left join condition
     * @param  string  $condition  possibly containing ?
     */
    public function joinWhere(string $tableChain, string $condition, ...$params): Selection
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
     * Adds order clause, more calls appends to the end.
     * @param  string  $columns  for example 'column1, column2 DESC'
     */
    public function order(string $columns, ...$params): Selection
    {
        $this->selection->order($columns, ...$params);
        return $this;
    }

    /**
     * Sets limit clause, more calls rewrite old values.
     */
    public function limit(?int $limit, ?int $offset = null): Selection
    {
        $this->selection->limit($limit, $offset);
        return $this;
    }

    /**
     * Sets offset using page number, more calls rewrite old values
     */
    public function page(int $page, int $itemsPerPage, int &$numOfPages = null): Selection
    {
        $this->selection->page($page, $itemsPerPage, $numOfPages);
        return $this;
    }

    /**
     * Sets group clause, more calls rewrite old value
     */
    public function group(string $columns, ...$params): Selection
    {
        $this->selection->group($columns, ...$params);
        return $this;
    }

    /**
     * Sets having clause, more calls rewrite old value
     */
    public function having(string $having, ...$params): Selection
    {
        $this->selection->having($having, ...$params);
        return $this;
    }

    /**
     * Aliases table. Example ':book:book_tag.tag', 'tg'
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
     * Executes aggregation function.
     * @param  string  $function  select call in "FUNCTION(column)" format
     */
    public function aggregation(string $function, ?string $groupFunction = null): float
    {
        return (float) $this->selection->aggregation($function, $groupFunction);
    }

    /**
     * Counts number of rows.
     * @param ?string $column  if it is not provided returns count of result rows, otherwise runs new sql counting query
     */
    public function count(?string $column = null): int
    {
        return $this->selection->count($column);
    }

    /**
     * Returns minimum value from a column.
     */
    public function min(string $column): float
    {
        return (float) $this->selection->min($column);
    }

    /**
     * Returns maximum value from a column.
     */
    public function max(string $column): float
    {
        return (float) $this->selection->max($column);
    }

    /**
     * Returns sum of values in a column
     */
    public function sum(string $column): float
    {
        return (float) $this->selection->sum($column);
    }

    /**********************************************************************\
     * Wrapper function - manipulation
    \**********************************************************************/

    /**
     * Inserts row in a table.
     * @param  array|\Traversable|Selection  $data  [$column => $value]|\Traversable|Selection for INSERT ... SELECT
     * @return ActiveRow|int|bool Returns ActiveRow or number of affected rows for Selection or table without primary key
     */
    public function insert(iterable $data)
    {
        $insertResult = $this->selection->insert($data);
        return $insertResult instanceof NetteDatabaseActiveRow ? $this->prepareRecord($insertResult) : $insertResult;
    }

    /**
     * Updates all rows in result set.
     * Joins in UPDATE are supported only in MySQL
     * @return int number of affected rows
     */
    public function update(iterable $data): int
    {
        return $this->selection->update($data);
    }

    /**
     * Deletes all rows in result set.
     * @return int number of affected rows
     */
    public function delete(): int
    {
        return $this->selection->delete();
    }

    /**********************************************************************\
     * Iterator interface
    \**********************************************************************/

    public function rewind(): void
    {
        $this->selection->rewind();
    }

    /**
     * @return ActiveRow|null
     */
    public function current(): ?ActiveRow
    {
        $row = $this->selection->current();
        return $row instanceof NetteDatabaseActiveRow ? $this->prepareRecord($row) : null;
    }

    /**
     * @return string|int Row ID
     */
    public function key(): mixed
    {
        return $this->selection->key();
    }

    public function next(): void
    {
        $this->selection->next();
    }

    public function valid(): bool
    {
        return $this->selection->valid();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    public function offsetSet($key, $value): void
    {
        $this->selection->offsetSet($key, $value);
    }

    public function offsetGet($key): ?ActiveRow
    {
        $row = $this->selection->offsetGet($key);
        return $row instanceof NetteDatabaseActiveRow ? $this->prepareRecord($row) : null;
    }

    public function offsetExists($key): bool
    {
        return $this->selection->offsetExists($key);
    }

    public function offsetUnset($key): void
    {
        $this->selection->offsetUnset($key);
    }

    /**********************************************************************\
     * Build methods
    \**********************************************************************/

    protected function prepareRecord(NetteDatabaseActiveRow $row): ActiveRow
    {
        $recordClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $recordClass($row, $this->structure);
    }

    protected function prepareRecords(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->prepareRecord($row);
        }
        return $result;
    }
}
