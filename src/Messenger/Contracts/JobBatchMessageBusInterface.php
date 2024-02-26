<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger\Contracts;

interface JobBatchMessageBusInterface
{
    /**
     * @param array<mixed> $messages
     * @param array<mixed> context
     */
    public function dispatchJobBatch(string $name, array $messages, string $handlerClass, array $context = []): void;
}
