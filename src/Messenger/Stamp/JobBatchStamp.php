<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger\Stamp;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchId;
use Symfony\Component\Messenger\Stamp\StampInterface;

final class JobBatchStamp implements StampInterface
{
    private string $jobId;

    public function __construct(JobBatchId $jobId)
    {
        $this->jobId = $jobId->toString();
    }

    public function getJobBatchId(): JobBatchId
    {
        return JobBatchId::fromString($this->jobId);
    }
}
