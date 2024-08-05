<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\EventSubscriber;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatch;
use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchManager;
use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchHandlerInterface;
use Pixelplan\MessengerJobBatchBundle\Messenger\Stamp\JobBatchStamp;
use Pixelplan\MessengerJobBatchBundle\Repository\JobRepository;
use Pixelplan\MessengerJobBatchBundle\Storage\Exception\JobBatchNotFound;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class WorkerMessageSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var iterable<JobBatchHandlerInterface>
     */
    private iterable $jobBatchHandlers;

    private JobBatchManager $jobBatchManager;

    /**
     * @param iterable<JobBatchHandlerInterface> $jobBatchHandlers
     */
    public function __construct(iterable $jobBatchHandlers, JobBatchManager $jobBatchManager)
    {
        $this->jobBatchHandlers = $jobBatchHandlers;
        $this->jobBatchManager = $jobBatchManager;
        $this->logger = new NullLogger();
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageHandledEvent::class => ['onMessageHandled', 0],
            // must have lower priority than SendFailedMessageForRetryListener
            WorkerMessageFailedEvent::class => ['onMessageFailed', 0],
        ];
    }

    public function onMessageHandled(WorkerMessageHandledEvent $event): void
    {
        $envelope = $event->getEnvelope();

        /** @var null|JobBatchStamp $jobBatchStamp */
        $jobBatchStamp = $envelope->last(JobBatchStamp::class);

        if (null === $jobBatchStamp) {
            // it's not a batch job, ignore it
            return;
        }

        try {
            $jobBatch = $this->jobBatchManager->batchJobHandledWithSuccess($jobBatchStamp->getJobBatchId());
        } catch (JobBatchNotFound) {
            return;
        }

        if (!$jobBatch->hasFinished()) {
            // the job batch is still running
            return;
        }

        $this->processFinishedJobBatch($jobBatch);
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();

        if ($event->willRetry()) {
            // message will be retried, ignore it
            return;
        }

        /** @var null|JobBatchStamp $jobBatchStamp */
        $jobBatchStamp = $envelope->last(JobBatchStamp::class);

        if (null === $jobBatchStamp) {
            // it's not a batch job, ignore it
            return;
        }

        $jobBatch = $this->jobBatchManager->batchJobHandledWithFailure($jobBatchStamp->getJobBatchId());

        if (!$jobBatch->hasFinished()) {
            // the job batch is still running
            return;
        }

        $this->processFinishedJobBatch($jobBatch);
    }

    private function processFinishedJobBatch(JobBatch $jobBatch): void
    {
        if (!$jobBatch->hasFinished()) {
            return;
        }

        $finishedSuccessfully = !$jobBatch->hasFailedJobs();

        $jobBatchHandler = $this->getJobBatchHandler($jobBatch);

        if ($finishedSuccessfully) {
            $this->info('Job batch "{jobBatchName}" ({jobBatchId}) finished successfully.', [
                'jobBatchId' => $jobBatch->getId()->toString(),
                'jobBatchName' => $jobBatch->getName(),
            ]);
            $jobBatchHandler->jobBatchSuccessful($jobBatch);
        } else {
            $this->info('Job batch "{jobBatchName}" ({jobBatchId}) finished with errors.', [
                'jobBatchId' => $jobBatch->getId()->toString(),
                'jobBatchName' => $jobBatch->getName(),
            ]);
            $jobBatchHandler->jobBatchFailed($jobBatch);
        }

        $this->jobBatchManager->remove($jobBatch->getId());
    }

    private function getJobBatchHandler(JobBatch $jobBatch): JobBatchHandlerInterface
    {
        foreach ($this->jobBatchHandlers as $handler) {
            if (get_class($handler) === $jobBatch->getHandlerClass()) {
                return $handler;
            }
        }

        throw new \RuntimeException('Handler not found.');
    }

    /**
     * @param array<mixed> $context
     */
    public function info(string $message, array $context): void
    {
        if($this->logger === null){
            return;
        }

        $this->logger->info($message, $context);
    }
}
