<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class BaseService
{
    /**
     * Execute a callback within a database transaction.
     *
     * @throws Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        try {
            return DB::transaction($callback);
        } catch (Throwable $e) {
            $this->handleException($e);
            throw $e;
        }
    }

    /**
     * Handle exceptions uniformly.
     */
    protected function handleException(Throwable $e): void
    {
        Log::error('Service Error: '.$e->getMessage(), [
            'service' => static::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
