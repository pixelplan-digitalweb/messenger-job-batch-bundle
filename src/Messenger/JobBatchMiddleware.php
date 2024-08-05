<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchManager;
use Pixelplan\MessengerJobBatchBundle\Messenger\Stamp\JobBatchStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class JobBatchMiddleware implements MiddlewareInterface
{
    private JobBatchManager $jobBatchManager;

    public function __construct(JobBatchManager $jobBatchManager)
    {
        $this->jobBatchManager = $jobBatchManager;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var JobBatchStamp|null $jobBatchStamp */
        $jobBatchStamp = $envelope->last(JobBatchStamp::class);

        if (null === $jobBatchStamp) {
            // it's not a job batch message, ignore
            return $stack->next()->handle($envelope, $stack);
        }

        if ($this->jobBatchManager->exists($jobBatchStamp->getJobBatchId())) {
            return $stack->next()->handle($envelope, $stack);
        }

        $envelope = $envelope->with(new HandledStamp(null, 'Ignored due to non-existent job batch ID'));

        return $envelope;
    }
}
