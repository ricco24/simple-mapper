<?php

namespace SimpleMapper;

use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use ArrayIterator;
use IteratorAggregate;
use Nette\Database\Table\IRow;
use Nette\DeprecatedException;

class ActiveRow implements IteratorAggregate, IRow
{
    /** @var NetteDatabaseActiveRow */
    protected $record;

    /** @var Structure */
    protected $structure;

    /**
     * @param NetteDatabaseActiveRow $record
     * @param Structure $structure
     */
    public function __construct(NetteDatabaseActiveRow $record, Structure $structure)
    {
        $this->record = $record;
        $this->structure = $structure;
    }

    /**
     * @param string $name
     * @return mixed|ActiveRow
     */
    public function __get($name)
    {
        $result = $this->record->$name;
        return $result instanceof IRow ? $this->prepareRecord($result) : $result;
    }

    /**
     * @return NetteDatabaseActiveRow
     */
    public function getRecord()
    {
        return $this->record;
    }

    /**
     * @return NetteDatabaseSelection
     */
    public function getTable()
    {
        return $this->record->getTable();
    }

    /**
     * @param NetteDatabaseSelection $selection
     */
    public function setTable(NetteDatabaseSelection $selection)
    {
        trigger_error('Internal IRow interface method', E_USER_NOTICE);
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

    /**
     * Returns referenced row
     * @param string $key
     * @param string $throughColumn
     * @return IRow|null
     */
    public function ref($key, $throughColumn = null)
    {
        $row = $this->record->ref($key, $throughColumn);
        return $row instanceof IRow ? $this->prepareRecord($row) : $row;
    }


    /**
     * Returns referencing rows
     * @param string $key
     * @param string $throughColumn
     * @return mixed
     */
    public function related($key, $throughColumn = null)
    {
        $selection = $this->record->related($key, $throughColumn);
        return $selection instanceof NetteDatabaseSelection ? $this->prepareSelection($selection) : $selection;
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
     * Stores value in column
     * @param string $key   column name
     * @param string $value
     * @throws DeprecatedException
     */
    public function offsetSet($key, $value)
    {
        $this->record->offsetSet($key, $value);
    }

    /**
     * Removes column from data
     * @param string $key column name
     * @throws DeprecatedException
     */
    public function offsetUnset($key)
    {
        $this->record->offsetUnset($key);
    }

    /**********************************************************************\
     * Extend methods
    \**********************************************************************/

    /**
     * Returns mm referencing rows
     * @param Selection $selection
     * @param string $ref
     * @param string $refPrimary
     * @return array
     */
    protected function mmRelated(Selection $selection, $ref, $refPrimary = 'id')
    {
        $result = [];
        foreach ($selection as $row) {
            $result[$row->ref($ref)->$refPrimary] = $row->ref($ref);
        }
        return $result;
    }

    /**********************************************************************\
     * Build methods
    \**********************************************************************/

    /**
     * Prepare one record
     * @param IRow $row
     * @return mixed
     */
    protected function prepareRecord(IRow $row)
    {
        $recordClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $recordClass($row, $this->structure);
    }

    /**
     * Prepare selection
     * @param NetteDatabaseSelection $selection
     * @return mixed
     */
    protected function prepareSelection(NetteDatabaseSelection $selection)
    {
        $selectionClass = $this->structure->getSelectionClass($selection->getName());
        return new $selectionClass($selection, $this->structure);
    }
}
