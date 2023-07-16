<?php

declare(strict_types=1);

namespace SimpleMapper;

use Iterator;
use IteratorAggregate;
use Nette\Database\Table\ActiveRow as NetteDatabaseActiveRow;
use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection as NetteDatabaseSelection;
use Nette\MemberAccessException;
use SimpleMapper\Exception\ActiveRowException;
use SimpleMapper\Exception\DeprecatedException;
use SimpleMapper\Structure\Structure;

class ActiveRow implements IteratorAggregate, IRow
{
    protected NetteDatabaseActiveRow $record;

    protected Structure $structure;

    protected ?ActiveRow $referencingRecord = null;

    /**
     * @param NetteDatabaseActiveRow $record
     * @param Structure $structure
     */
    public function __construct(NetteDatabaseActiveRow $record, Structure $structure)
    {
        $this->record = $record;
        $this->structure = $structure;
    }

    /**********************************************************************\
     * Wrapper function
    \**********************************************************************/

    /**
     * @throws ActiveRowException
     */
    public function setTable(NetteDatabaseSelection $selection): void
    {
        throw new ActiveRowException('Internal ActiveRow interface method');
    }

    public function getTable(): NetteDatabaseSelection
    {
        return $this->record->getTable();
    }

    public function __toString(): string
    {
        return (string) $this->record;
    }

    public function toArray(): array
    {
        return $this->record->toArray();
    }

    public function getPrimary(bool $throw = true): mixed
    {
        return $this->record->getPrimary($throw);
    }

    public function getSignature(bool $throw = true): string
    {
        return $this->record->getSignature($throw);
    }

    public function ref(string $key, ?string $throughColumn = null): ?self
    {
        $row = $this->record->ref($key, $throughColumn);
        if ($row instanceof NetteDatabaseActiveRow) {
            $result = $this->prepareRecord($row);
            $result->setReferencingRecord($this);
            return $result;
        }

        return null;
    }

    public function related(string $key, ?string $throughColumn = null): Selection
    {
        return $this->prepareSelection($this->record->related($key, $throughColumn));
    }

    public function update(iterable $data): bool
    {
        return $this->record->update($data);
    }

    public function delete(): int
    {
        return $this->record->delete();
    }

    /**********************************************************************\
     * IteratorAggregate interface
    \**********************************************************************/

    public function getIterator(): Iterator
    {
        return $this->record->getIterator();
    }

    /**********************************************************************\
     * ArrayAccess interface
    \**********************************************************************/

    /**
     * Stores value in column.
     * @param  string  $column
     * @param  mixed  $value
     */
    public function offsetSet($column, $value): void
    {
        $this->record->offsetSet($column, $value);
    }

    /**
     * Returns value of column.
     * @param  string  $column
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($column)
    {
        $result = $this->record->offsetGet($column);
        return $result instanceof NetteDatabaseActiveRow ? $this->prepareRecord($result) : $result;
    }

    /**
     * Tests if column exists.
     * @param  string  $column
     */
    public function offsetExists($column): bool
    {
        return $this->record->offsetExists($column);
    }

    /**
     * Removes column from data.
     * @param  string  $column
     */
    public function offsetUnset($column): void
    {
        $this->record->offsetUnset($column);
    }

    /**********************************************************************\
     * Magic accessors
    \**********************************************************************/

    /**
     * @throws DeprecatedException
     */
    public function __set($column, $value)
    {
        throw new DeprecatedException('ActiveRow is read-only; use update() method instead.');
    }

    /**
     * @return mixed|ActiveRow
     * @throws MemberAccessException
     */
    public function &__get(string $key)
    {
        $result = $this->record->$key;
        if ($result instanceof NetteDatabaseActiveRow) {
            $result = $this->prepareRecord($result);
        }
        return $result;
    }

    public function __isset($key)
    {
        return isset($this->record->$key);
    }

    /**
     * @throws DeprecatedException
     */
    public function __unset($key)
    {
        throw new DeprecatedException('ActiveRow is read-only.');
    }

    /**********************************************************************\
     * Extend methods
    \**********************************************************************/

    /**
     * @param ActiveRow $row
     */
    public function setReferencingRecord(ActiveRow $row): void
    {
        $this->referencingRecord = $row;
    }

    /**
     * @return null|ActiveRow
     */
    public function getReferencingRecord(): ?ActiveRow
    {
        return $this->referencingRecord;
    }

    /**
     * Returns mm referencing rows
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

    protected function prepareRecord(NetteDatabaseActiveRow $row): ActiveRow
    {
        $recordClass = $this->structure->getActiveRowClass($row->getTable()->getName());
        return new $recordClass($row, $this->structure);
    }

    protected function prepareSelection(NetteDatabaseSelection $selection): Selection
    {
        $selectionClass = $this->structure->getSelectionClass($selection->getName());
        return new $selectionClass($selection, $this->structure);
    }

    /**********************************************************************\
     * Help methods
    \**********************************************************************/

    public function getRecord(): NetteDatabaseActiveRow
    {
        return $this->record;
    }
}
