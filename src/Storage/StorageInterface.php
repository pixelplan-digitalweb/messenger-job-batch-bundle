<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Storage;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatch;
use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchId;
use Pixelplan\MessengerJobBatchBundle\Storage\Exception\JobBatchNotFound;

interface StorageInterface
{
    public function save(JobBatch $jobBatch): void;

    /**
     * @throws JobBatchNotFound
     */
    public function fetch(JobBatchId $jobBatchId): JobBatch;

    public function delete(JobBatchId $jobBatchId): void;
}
