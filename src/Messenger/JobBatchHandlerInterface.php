<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatch;

interface JobBatchHandlerInterface
{
    public function jobBatchSuccessful(JobBatch $jobBatch): void;
    public function jobBatchFailed(JobBatch $jobBatch): void;
}
