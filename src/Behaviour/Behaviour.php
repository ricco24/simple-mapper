<?php

declare(strict_types=1);

namespace SimpleMapper\Behaviour;

use SimpleMapper\ActiveRow;

interface Behaviour
{
    /**
     * Transform data before insert
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function beforeInsert(array $data): array;

    /**
     * Handle after insert
     * @param array<string, mixed> $data
     */
    public function afterInsert(ActiveRow $record, array $data): void;

    /**
     * Transform data before update
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    public function beforeUpdate(ActiveRow $record, array $data): array;

    /**
     * Handle after update
     * @param array<string, mixed> $data
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
