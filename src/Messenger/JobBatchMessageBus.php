<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger;

use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchManager;
use Pixelplan\MessengerJobBatchBundle\Messenger\Contracts\JobBatchMessageBusInterface;
use Pixelplan\MessengerJobBatchBundle\Messenger\Stamp\JobBatchStamp;
use Pixelplan\MessengerJobBatchBundle\Model\JobBatch;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Exception;
use ReflectionClass;

final class JobBatchMessageBus implements JobBatchMessageBusInterface
{
    private JobBatchManager $jobBatchManager;

    private MessageBusInterface $messageBus;

    public function __construct(JobBatchManager $jobBatchManager, MessageBusInterface $messageBus)
    {
        $this->jobBatchManager = $jobBatchManager;
        $this->messageBus = $messageBus;
    }

    /**
     * @param array<mixed> $messages
     * @param array<mixed> $context
     */
    public function dispatchJobBatch(string $name, array $messages, string $handlerClass, array $context = []): void
    {
        $this->validateHandlerClass($handlerClass);

        $jobBatch = $this->jobBatchManager->create($name, count($messages), $handlerClass, $context);

        foreach ($messages as $message) {
            $this->messageBus->dispatch($message, [
                new JobBatchStamp($jobBatch->getId())
            ]);
        }
    }

    private function validateHandlerClass(string $handlerClass): void
    {
        if (!class_exists($handlerClass))
        {
            throw new Exception(sprintf('The class %s does not exist.', $handlerClass));
        }

        $handlerClass = new ReflectionClass($handlerClass);

        if (!$handlerClass->implementsInterface(JobBatchHandlerInterface::class))
        {
            throw new Exception(sprintf('The class %s does not implement %s.', $handlerClass, JobBatchHandlerInterface::class));
        }
    }
}
