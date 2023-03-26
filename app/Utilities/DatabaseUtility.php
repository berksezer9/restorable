<?php

namespace App\Utilities;

use Illuminate\Support\Facades\DB;

class DatabaseUtility
{
    /**
     * @param callable $callable (the function to be executed)
     * @param array $arguments
     * @return mixed
     * @throws \Throwable
     */
    public static function executeTransaction(callable $callable, array $arguments = []): mixed
    {
        DB::beginTransaction();

        try
        {
            $return = $callable(...$arguments);

            DB::commit();

            return $return;
        }
        catch (\Throwable $throwable)
        {
            DB::rollBack();

            throw $throwable;
        }
    }
}
