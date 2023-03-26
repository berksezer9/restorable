<?php

namespace Src\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @mixin Model
 */
trait Restorable
{
    /**
     * The trash table into which deleted rows will be inserted.
     *
     * @var string
     */
    protected string $trashTable;

    protected bool $deleteWithoutInsertingIntoTrash = false;

    /**
     * @return string
     */
    public function getTrashTable(): string
    {
        return $this->trashTable;
    }

    /**
     * @return string
     */
    public function setTrashTable(): string
    {
        return $this->trashTable;
    }

    /**
     * @return bool
     */
    public function getDeleteWithoutInsertingIntoTrash(): bool
    {
        return $this->deleteWithoutInsertingIntoTrash;
    }

    /**
     * @param bool $deleteWithoutInsertingIntoTrash
     */
    public function setDeleteWithoutInsertingIntoTrash(bool $deleteWithoutInsertingIntoTrash): void
    {
        $this->deleteWithoutInsertingIntoTrash = $deleteWithoutInsertingIntoTrash;
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel(): void
    {
        $this->getDeleteWithoutInsertingIntoTrash()
            ? $this->deleteWithoutInsertingIntoTrash()
            : $this->insertIntoTrashAndDelete();
    }

    /**
     * Inserts a corresponding row into the trash table, then deletes from the main table.
     * @return void
     */
    protected function insertIntoTrashAndDelete(): void
    {
        $this->insertIntoTrash();

        $this->deleteFromTable();
    }

    /**
     * Deletes from the main table without inserting a corresponding row into the trash table.
     * @return bool
     */
    protected function deleteWithoutInsertingIntoTrash(): bool
    {
        $this->setDeleteWithoutInsertingIntoTrash(deleteWithoutInsertingIntoTrash: true);

        $this->delete();

        return true;
    }

    /**
     * Perform the actual delete query on this model instance.
     * (exact copy of \Illuminate\Database\Eloquent\Model::performDeleteOnModel)
     *
     * @return void
     */
    protected function deleteFromTable(): void
    {
        $this->setKeysForSaveQuery($this->newModelQuery())->delete();

        $this->exists = false;
    }

    /**
     * Inserts a corresponding row into the trash table.
     * @return void
     */
    protected function insertIntoTrash(): void
    {
        //gets the original values lest the Model object has been updated without being saved to the database.
        DB::table(table: $this->getTrashTable())->insert(values: $this->getRawOriginal());
    }

    public static function restore(mixed $id): void
    {
        $dummyObject = (new static);

        $values = DB::table(table: $dummyObject->getTrashTable())->find(id: $id)->get();

        DB::table(table: $dummyObject->getTable())->insert(values: $values->all());

        DB::table(table: $dummyObject->getTrashTable())->delete(id: $id);
    }
}
