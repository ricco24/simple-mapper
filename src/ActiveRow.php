<?php

namespace SimpleMapper;

use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Nette\Database\Table\IRow;

class ActiveRow implements ArrayAccess, IteratorAggregate
{
    /** @var NetteDatabaseActiveRow */
    protected $record;

    /**
     * @param NetteDatabaseActiveRow $record
     */
    public function __construct(NetteDatabaseActiveRow $record)
    {
        $this->record = $record;
    }

    /**
     * @param string $name
     * @return mixed|NetteDatabaseActiveRow
     */
    public function __get($name)
    {
        // Try to find record property
        return $this->record->$name;
    }

    /**
     * @return NetteDatabaseActiveRow
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**********************************************************************\
     * Wrapper function
    \**********************************************************************/

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->record;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->record->toArray();
    }

    /**
     * @param bool|true $need
     * @return mixed
     */
    public function getPrimary($need = true)
    {
        return $this->record->getPrimary($need);
    }

    /**
     * @param bool|true $need
     * @return string
     */
    public function getSignature($need = true)
    {
        return $this->record->getSignature($need);
    }

    /**
     * @param array|\Traversable $data
     * @return bool
     */
    public function update($data)
    {
        return $this->record->update($data);
    }

    /**
     * @return int
     */
    public function delete()
    {
        return $this->record->delete();
    }

    /**********************************************************************\
     * IteratorAggregate interface
    \**********************************************************************/

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->record->getIterator();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    /**
     * Stores value in column
     * @param string $key   column name
     * @param string $value
     */
    public function offsetSet($key, $value)
    {
        $this->record->offsetSet($key, $value);
    }

    /**
     * Returns value of column
     * @param string $key  column name
     * @return string
     */
    public function offsetGet($key)
    {
        return $this->record->offsetGet($key);
    }

    /**
     * Tests if column exists
     * @param string $key   column name
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->record->offsetExists($key);
    }

    /**
     * Removes column from data
     * @param string $key column name
     */
    public function offsetUnset($key)
    {
        $this->record->offsetUnset($key);
    }

    /**********************************************************************\
     * referenced / related functions
    \**********************************************************************/

    /**
     * @param string $key
     * @param string $recordClass
     * @param null|string $throughColumn
     * @return mixed
     */
    protected function getReference($key, $recordClass, $throughColumn = null)
    {
        $row = $this->record->ref($key, $throughColumn);
        return $row instanceof IRow ? new $recordClass($row) : $row;
    }

    /**
     * @param string $key
     * @param string $selectionClass
     * @param null|string $throughColumn
     * @return mixed
     */
    protected function getRelated($key, $selectionClass, $throughColumn = null)
    {
        return new $selectionClass($this->record->related($key, $throughColumn));
    }

    /**
     * @param string $key
     * @param string $foreignName
     * @param string $recordClass
     * @param null|string $throughColumn
     * @return array
     */
    protected function getMMRelated($key, $foreignName, $recordClass, $throughColumn = null)
    {
        $result = [];
        foreach ($this->record->related($key, $throughColumn) as $row) {
            $result[] = new $recordClass($row->$foreignName);
        }
        return $result;
    }

    /**
     * @param IRow $row
     * @param string $recordClass
     * @return mixed
     */
    protected function prepareRecord(IRow $row, $recordClass)
    {
        return new $recordClass($row);
    }
}
