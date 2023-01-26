<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\Messenger\Contracts;

use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchMessageBus;
use Symfony\Component\Messenger\MessageBusInterface;

interface JobBatchMessageBusFactoryInterface
{
    public function forMessageBus(MessageBusInterface $messageBus): JobBatchMessageBus;
}
