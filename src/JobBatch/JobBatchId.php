<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\JobBatch;

final class JobBatchId
{
    private string $jobBatchId;

    private function __construct(string $jobBatchId)
    {
        $this->jobBatchId = $jobBatchId;
    }

    public static function fromString(string $jobBatchId): self
    {
        return new self($jobBatchId);
    }

    public function toString(): string
    {
        return $this->jobBatchId;
    }

    public function __toString(): string
    {
        return $this->jobBatchId;
    }
}
