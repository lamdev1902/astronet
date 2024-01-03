<?php
/**
 * Copyright © 2019-2023 Rhubarb Tech Inc. All Rights Reserved.
 *
 * The Object Cache Pro Software and its related materials are property and confidential
 * information of Rhubarb Tech Inc. Any reproduction, use, distribution, or exploitation
 * of the Object Cache Pro Software and its related materials, in whole or in part,
 * is strictly forbidden unless prior permission is obtained from Rhubarb Tech Inc.
 *
 * In addition, any reproduction, use, distribution, or exploitation of the Object Cache Pro
 * Software and its related materials, in whole or in part, is subject to the End-User License
 * Agreement accessible in the included `LICENSE` file, or at: https://objectcache.pro/eula
 */

declare(strict_types=1);

namespace RedisCachePro\Clients\Concerns;

use Throwable;
use LogicException;

use RedisCachePro\Clients\Transaction;

trait PhpRedisTransactions
{
    /**
     * Hijack pipeline calls to trace them.
     *
     * @return \RedisCachePro\Clients\Transaction
     */
    public function pipeline()
    {
        return new Transaction($this, self::PIPELINE);
    }

    /**
     * Hijack multi calls to trace them.
     *
     * @param  int  $mode
     * @return \RedisCachePro\Clients\Transaction
     */
    public function multi(int $mode = self::MULTI)
    {
        return new Transaction($this, $mode);
    }

    /**
     * Block non-chained transactions.
     *
     * @return void
     */
    public function exec()
    {
        throw new LogicException('Non-chained transactions are not supported');
    }

    /**
     * Executes buffered transaction using client's callback.
     *
     * @phpstan-return mixed
     *
     * @param  \RedisCachePro\Clients\Transaction  $transaction
     * @return array<int, mixed>|bool
     */
    public function executeBufferedTransaction(Transaction $transaction)
    {
        $method = $transaction->context === self::MULTI ? 'multi' : 'pipeline';

        try {
            return $this->{$this->callback}(function () use ($transaction, $method) {
                $pipe = $this->client->{$method}();

                foreach ($transaction->commands as $command) {
                    $pipe->{$command[0]}(...$command[1]);
                }

                return $pipe->exec();
            }, 'exec');
        } catch (Throwable $th) {
            if ($this->client->getMode() !== self::ATOMIC) {
                $this->client->discard();
            }

            throw $th;
        }
    }
}
