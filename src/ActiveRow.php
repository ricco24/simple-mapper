<?php

declare(strict_types=1);

namespace SimpleMapper;

use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use ArrayIterator;
use IteratorAggregate;
use Nette\Database\Table\IRow;
use Nette\DeprecatedException;
use SimpleMapper\Exception\ActiveRowException;
use SimpleMapper\Structure\Structure;

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
    public function getRecord(): NetteDatabaseActiveRow
    {
        return $this->record;
    }

    /**
     * @return NetteDatabaseSelection
     */
    public function getTable(): NetteDatabaseSelection
    {
        return $this->record->getTable();
    }

    /**
     * @param NetteDatabaseSelection $selection
     * @throws ActiveRowException
     */
    public function setTable(NetteDatabaseSelection $selection): void
    {
        throw new ActiveRowException('Internal IRow interface method');
    }

    /**********************************************************************\
     * Wrapper function
    \**********************************************************************/

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->record;
    }

    /**
     * @return array
     */
    public function toArray(): array
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
    public function getSignature($need = true): string
    {
        return $this->record->getSignature($need);
    }

    /**
     * @param array|\Traversable $data
     * @return bool
     */
    public function update($data): bool
    {
        return $this->record->update($data);
    }

    /**
     * @return int
     */
    public function delete(): int
    {
        return $this->record->delete();
    }

    /**
     * Returns referenced row
     * @param string $key
     * @param string $throughColumn
     * @return ActiveRow|null
     */
    public function ref($key, $throughColumn = null): ?ActiveRow
    {
        $row = $this->record->ref($key, $throughColumn);
        return $row instanceof IRow ? $this->prepareRecord($row) : null;
    }


    /**
     * Returns referencing rows
     * @param string $key
     * @param string $throughColumn
     * @return Selection
     */
    public function related($key, $throughColumn = null): ?Selection
    {
        $selection = $this->record->related($key, $throughColumn);
        return $selection instanceof NetteDatabaseSelection ? $this->prepareSelection($selection) : null;
    }

    /**********************************************************************\
     * IteratorAggregate interface
    \**********************************************************************/

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return $this->record->getIterator();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    /**
     * Returns value of column
     * @param string $key  column name
     * @return mixed
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
    public function offsetExists($key): bool
    {
        return $this->record->offsetExists($key);
    }

    /**
     * Stores value in column
     * @param string $key   column name
     * @param string $value
     * @throws DeprecatedException
     */
    public function offsetSet($key, $value): void
    {
        $this->record->offsetSet($key, $value);
    }

    /**
     * Removes column from data
     * @param string $key column name
     * @throws DeprecatedException
     */
    public function offsetUnset($key): void
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
    protected function mmRelated(Selection $selection, string $ref, string $refPrimary = 'id'): array
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
     * @return ActiveRow
     */
    protected function prepareRecord(IRow $row): ActiveRow
    {
        $recordClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $recordClass($row, $this->structure);
    }

    /**
     * Prepare selection
     * @param NetteDatabaseSelection $selection
     * @return Selection
     */
    protected function prepareSelection(NetteDatabaseSelection $selection): Selection
    {
        $selectionClass = $this->structure->getSelectionClass($selection->getName());
        return new $selectionClass($selection, $this->structure);
    }
}
