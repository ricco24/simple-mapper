<?php

declare(strict_types=1);

namespace SimpleMapper\Behaviour;

use SimpleMapper\ActiveRow;

interface Behaviour
{
    /**
     * Transform data before insert
     */
    public function beforeInsert(array $data): array;

    /**
     * Handle after insert
     */
    public function afterInsert(ActiveRow $record, array $data): void;

    /**
     * Transform data before update
     */
    public function beforeUpdate(ActiveRow $record, array $data): array;

    /**
     * Handle after update
     */
    public function afterUpdate(ActiveRow $oldRecord, ActiveRow $newRecord, array $data): void;

    /**
     * Handle before delete
     */
    public function beforeDelete(ActiveRow $record, bool $soft): void;

    /**
     * Handle after delete
     */
    public function afterDelete(ActiveRow $record, bool $soft): void;
}
