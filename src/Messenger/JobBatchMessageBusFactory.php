<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchManager;
use Symfony\Component\Messenger\MessageBusInterface;

final class JobBatchMessageBusFactory
{
    private JobBatchManager $jobBatchManager;

    public function __construct(JobBatchManager $jobBatchManager)
    {
        $this->jobBatchManager = $jobBatchManager;
    }

    public function forMessageBus(MessageBusInterface $messageBus): JobBatchMessageBus
    {
        return new JobBatchMessageBus($this->jobBatchManager, $messageBus);
    }
}
