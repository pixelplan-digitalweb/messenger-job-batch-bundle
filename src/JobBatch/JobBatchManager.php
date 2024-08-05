<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\JobBatch;

use Pixelplan\MessengerJobBatchBundle\Storage\StorageInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use DateTimeImmutable;

final class JobBatchManager implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private StorageInterface $storage;

    private LockInterface $lock;

    public function __construct(StorageInterface $storage, LockFactory $lockFactory)
    {
        $this->storage = $storage;
        $this->lock = $lockFactory->createLock('job_batch_manager');
        $this->logger = new NullLogger();
    }

    /**
     * @param array<mixed> $context
     */
    public function create(string $name, int $totalJobs, string $handlerClass, array $context): JobBatch
    {
        $jobBatchId = JobBatchIdGenerator::new();
        $jobBatch = new JobBatch($jobBatchId, $name, $totalJobs, $handlerClass, $context, new DateTimeImmutable());

        $this->lock->acquire(true);

        try {
            $this->storage->save($jobBatch);
            $this->debug('Created new job batch with id "{jobBatchId}" having {totalJobs} jobs.', ['jobBatchId' => $jobBatchId->toString(), 'totalJobs' => $totalJobs]);
        } finally {
            $this->lock->release();
        }

        return $jobBatch;
    }

    public function batchJobHandledWithSuccess(JobBatchId $jobBatchId): JobBatch
    {
        $this->lock->acquire(true);

        try {
            $jobBatch = $this->storage->fetch($jobBatchId);
            $jobBatch->decreasePendingJobs();
            $this->storage->save($jobBatch);

            $this->debug('[{completed}/{total}] Job belonging to "{jobBatchId}" handled successful.', [
                'completed' => $jobBatch->getTotalCompletedJobs(),
                'total' => $jobBatch->getTotalJobs(),
                'jobBatchId' => $jobBatchId->toString()
            ]);
        } finally {
            $this->lock->release();
        }

        return $jobBatch;
    }

    public function batchJobHandledWithFailure(JobBatchId $jobBatchId): JobBatch
    {
        $this->lock->acquire(true);

        try {
            $jobBatch = $this->storage->fetch($jobBatchId);
            $jobBatch->decreasePendingJobs();
            $jobBatch->increaseFailedJobs();
            $this->storage->save($jobBatch);

            $this->debug('[{completed}/{total}] Job belonging to "{jobBatchId}" handled failed.', [
                'completed' => $jobBatch->getTotalCompletedJobs(),
                'total' => $jobBatch->getTotalJobs(),
                'jobBatchId' => $jobBatchId->toString()
            ]);
        } finally {
            $this->lock->release();
        }

        return $jobBatch;
    }

    public function remove(JobBatchId $jobBatchId): void
    {
        $this->lock->acquire(true);

        try {
            $this->storage->delete($jobBatchId);
            $this->debug('Job batch "{jobBatchId}" removed.', ['jobBatchId' => $jobBatchId->toString()]);
        } finally {
            $this->lock->release();
        }
    }

    public function exists(JobBatchId $jobBatchId): bool
    {
        return $this->storage->exists($jobBatchId);
    }

    /**
     * @param array<mixed> $context
     */
    public function debug(string $message, array $context): void
    {
        if ($this->logger === null) {
            return;
        }

        $this->logger->debug($message, $context);
    }
}
