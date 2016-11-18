<?php

namespace SimpleMapper;

use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\InvalidArgumentException;
use LogicException;
use ArrayAccess;
use Iterator;
use Countable;
use Traversable;

class Selection implements Iterator, Countable, ArrayAccess
{
    /** @var NetteDatabaseSelection */
    private $selection;

    /** @var string */
    protected $recordClass = '';

    /**
     * @param NetteDatabaseSelection $selection
     */
    public function __construct(NetteDatabaseSelection $selection)
    {
        $this->selection = $selection;
    }

    /**
     * @return NetteDatabaseSelection
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**********************************************************************\
     * Wrapper function - fetch
    \**********************************************************************/

    /**
     * Returns row specified by primary key
     * @param mixed $key    Primary key
     * @return mixed
     */
    public function get($key)
    {
        $row = $this->selection->get($key);
        return $row instanceof ActiveRow ? $this->prepareRecord($row) : $row;
    }

    /**
     * Returns one record
     * @return bool|mixed
     */
    public function fetch()
    {
        $row = $this->selection->fetch();
        return $row ? $this->prepareRecord($row) : $row;
    }

    /**
     * Fetches single field
     * @param string|null $column
     * @return mixed
     */
    public function fetchField($column = null)
    {
        return $this->selection->fetchField($column);
    }

    /**
     * Fetch key => value pairs
     * @param string|null $key
     * @param string|null $value
     * @return array
     */
    public function fetchPairs($key = null, $value = null)
    {
        $result = [];

        $pairs = $this->selection->fetchPairs($key, $value);
        foreach ($pairs as $k => $v) {
            $result[$k] = $v instanceof ActiveRow ? $this->prepareRecord($v) : $v;
        }
        return $result;
    }

    /**
     * Returns all records
     * @return array
     */
    public function fetchAll()
    {
        return $this->prepareRecords($this->selection->fetchAll());
    }

    /**
     * Some examples of usage: https://github.com/nette/utils/blob/master/tests%2FUtils%2FArrays.associate().phpt
     * @param string $path
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
    public function select($columns, ...$params)
    {
        $this->selection->select($columns, ...$params);
        return $this;
    }

    /**
     * Adds condition for primary key
     * @param mixed $key
     * @return Selection
     */
    public function wherePrimary($key)
    {
        $this->selection->wherePrimary($key);
        return $this;
    }

    /**
     * Adds where condition, more calls appends with AND
     * @param string $condition
     * @param mixed ...$params
     * @return Selection
     */
    public function where($condition, ...$params)
    {
        $this->selection->where($condition, ...$params);
        return $this;
    }

    /**
     * Adds ON condition when joining specified table, more calls appends with AND
     * @param string $tableChain    table chain or table alias for which you need additional left join condition
     * @param string $condition     condition possibly containing ?
     * @param mixed ...$params
     * @return Selection
     */
    public function joinWhere($tableChain, $condition, ...$params)
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
    public function whereOr(array $parameters)
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
    public function order($columns, ...$params)
    {
        $this->selection->order($columns, $params);
        return $this;
    }

    /**
     * Sets limit clause, more calls rewrite old values
     * @param int $limit
     * @param int $offset
     * @return Selection
     */
    public function limit($limit, $offset = null)
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
    public function page($page, $itemsPerPage, & $numOfPages = null)
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
    public function group($columns, ...$params)
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
    public function having($having, ...$params)
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
    public function alias($tableChain, $alias)
    {
        $this->selection->alias($tableChain, $alias);
        return $this;
    }

    /**********************************************************************\
     * Wrapper function - aggregations
    \**********************************************************************/

    /**
     * Executes aggregation function
     * @param string $function      select call in "FUNCTION(column)" format
     * @return string
     */
    public function aggregation($function)
    {
        return $this->selection->aggregation($function);
    }

    /**
     * Counts number of rows
     * Countable interface
     * @param string $column    If it is not provided returns count of result rows, otherwise runs new sql counting query
     * @return int
     */
    public function count($column = NULL)
    {
        return $this->selection->count($column);
    }

    /**
     * Returns minimum value from a column
     * @param string $column
     * @return int
     */
    public function min($column)
    {
        return $this->selection->min($column);
    }

    /**
     * Returns maximum value from a column
     * @param string $column
     * @return int
     */
    public function max($column)
    {
        return $this->selection->max($column);
    }

    /**
     * Returns sum of values in a column
     * @param string $column
     * @return int
     */
    public function sum($column)
    {
        return $this->selection->sum($column);
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
     * @param  array|Traversable $data      ($column => $value)
     * @return int
     */
    public function update($data)
    {
        return $this->selection->update($data);
    }

    /**
     * Deletes all rows in result set
     * @return int
     */
    public function delete()
    {
        return $this->selection->delete();
    }

    /**********************************************************************\
     * Iterator interface
    \**********************************************************************/

    /**
     * Rewind selection
     */
    public function rewind()
    {
        $this->selection->rewind();
    }

    /**
     * Returns current selection data record
     * @return bool|mixed
     */
    public function current()
    {
        $row = $this->selection->current();
        return $row instanceof IRow ? $this->prepareRecord($row) : false;
    }

    /**
     * Returns current selection data key
     * @return string
     */
    public function key()
    {
        return $this->selection->key();
    }

    /**
     * Move iterator
     */
    public function next()
    {
        $this->selection->next();
    }

    /**
     * It is selection valid
     * @return bool
     */
    public function valid()
    {
        return $this->selection->valid();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    /**
     * @param string $key   Row ID
     * @param IRow $value
     */
    public function offsetSet($key, $value)
    {
        $this->selection->offsetSet($key, $value);
    }

    /**
     * Returns specified row
     * @param string $key   Row ID
     * @return IRow|null
     */
    public function offsetGet($key)
    {
        $row = $this->selection->offsetGet($key);
        return $row instanceof IRow ? $this->prepareRecord($row) : $row;
    }

    /**
     * Tests if row exists
     * @param string $key   Row ID
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->selection->offsetExists($key);
    }

    /**
     * Removes row from result set
     * @param string $key   Row ID
     */
    public function offsetUnset($key)
    {
        $this->selection->offsetUnset($key);
    }

    /**********************************************************************\
     * Internal methods
    \**********************************************************************/

    /**
     * Prepare one record
     * @param IRow $row
     * @return mixed
     */
    private function prepareRecord(IRow $row)
    {
        $recordClass = $this->getRecordClass();
        return new $recordClass($row);
    }

    /**
     * Prepare records array
     * @param array $rows
     * @return array
     */
    private function prepareRecords(array $rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->prepareRecord($row);
        }
        return $result;
    }

    /**
     * Get defined record class or throw exception if class is not defined
     * @return string
     */
    private function getRecordClass()
    {
        if (empty($this->recordClass)) {
            throw new LogicException('$recordClass parameter has to be set in child class');
        }
        return $this->recordClass;
    }
}
