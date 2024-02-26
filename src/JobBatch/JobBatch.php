<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\JobBatch;

use DateTimeImmutable;

final class JobBatch
{
    private JobBatchId $id;

    private string $name;

    private int $totalJobs;

    private int $pendingJobs;

    private int $failedJobs;

    private string $handlerClass;

    /**
     * @var array<mixed>
     */
    private array $context;

    private DateTimeImmutable $createdAt;

    /**
     * @param array<mixed> $context
     */
    public function __construct(JobBatchId $jobId, string $name, int $totalJobs, string $handlerClass, array $context, DateTimeImmutable $createdAt)
    {
        $this->id = $jobId;
        $this->name = $name; 
        $this->totalJobs = $totalJobs;
        $this->pendingJobs = $totalJobs;
        $this->failedJobs = 0;
        $this->handlerClass = $handlerClass;
        $this->context = $context;        
        $this->createdAt = $createdAt;
    }

    public function getId(): JobBatchId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function decreasePendingJobs(): void
    {
        $this->pendingJobs--;
    }

    public function increaseFailedJobs(): void
    {
        $this->failedJobs++;
    }

    public function hasFinished(): bool
    {
        return 0 === $this->pendingJobs;
    }

    public function hasFailedJobs(): bool
    {
        return $this->failedJobs > 0;
    }

    public function getTotalCompletedJobs(): int
    {
        return $this->totalJobs - $this->pendingJobs;
    }

    public function getTotalJobs(): int
    {
        return $this->totalJobs;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * @return array<mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }
}
