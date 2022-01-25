<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger;

use Pixelplan\MessengerJobBatchBundle\Messenger\Stamp\JobBatchStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

final class JobBatchMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var JobBatchStamp|null $jobBatchStamp */
        $jobBatchStamp = $envelope->last(JobBatchStamp::class);

        /** @var ReceivedStamp|null $receivedStamp */
        $receivedStamp = $envelope->last(ReceivedStamp::class);

        if (null === $jobBatchStamp || null === $receivedStamp) {
            dump('no job batch stamp or handled stamp');
            return $stack->next()->handle($envelope, $stack);
        }

        $result = $stack->next()->handle($envelope, $stack);

        $handledStamp = $result->last(HandledStamp::class);

        $sentToFailureStamp = $result->last(SentToFailureTransportStamp::class);

        if (null !== $sentToFailureStamp) {
            dd('sent to failure');
        }

        return $result;
    }
}
