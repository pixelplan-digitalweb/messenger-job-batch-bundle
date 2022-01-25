<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Storage\Exception;

use Exception;
use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchId;

final class JobBatchNotFound extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function byId(JobBatchId $jobBatchId): self
    {
        return new self(sprintf('JobBatch with id "%s" not found', $jobBatchId));
    }
}
