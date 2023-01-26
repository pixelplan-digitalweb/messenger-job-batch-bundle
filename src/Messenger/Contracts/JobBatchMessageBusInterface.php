<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger\Contracts;

interface JobBatchMessageBusInterface
{
    /**
     * @param array<mixed> $messages
     */
    public function dispatchJobBatch(string $name, array $messages, string $handlerClass): void;
}
