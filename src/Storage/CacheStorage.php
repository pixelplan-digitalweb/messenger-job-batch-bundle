<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Storage;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatch;
use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchId;
use Pixelplan\MessengerJobBatchBundle\Storage\Exception\JobBatchNotFound;
use Psr\Cache\CacheItemPoolInterface;

final class CacheStorage implements StorageInterface
{
    private CacheItemPoolInterface $pool;

    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    public function save(JobBatch $jobBatch): void
    {
        $cacheItem = $this->pool->getItem($this->getKey($jobBatch->getId()));
        $cacheItem->set($jobBatch);

        $this->pool->save($cacheItem);
    }

    public function fetch(JobBatchId $jobBatchId): JobBatch
    {
        $cacheItem = $this->pool->getItem($this->getKey($jobBatchId));
        $value = $cacheItem->get();

        if ($value instanceof JobBatch) {
            return $value;
        }

        throw JobBatchNotFound::byId($jobBatchId);
    }

    public function delete(JobBatchId $jobBatchId): void
    {
        $this->pool->deleteItem($this->getKey($jobBatchId));
    }

    private function getKey(JobBatchId $jobBatchId): string
    {
        return $jobBatchId->toString();
    }
}
