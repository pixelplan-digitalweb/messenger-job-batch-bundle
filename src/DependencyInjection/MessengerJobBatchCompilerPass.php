<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\DependencyInjection;

use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchHandlerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class MessengerJobBatchCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('pixelplan_messenger_job_batch.worker_message_subscriber');

        $jobBatchMessageHandlerReferences = [];

        foreach ($container->findTaggedServiceIds('pixelplan_messenger_job_batch.job_batch_handler') as $id => $tags) {
            $jobBatchMessageHandlerReferences[] = new Reference($id);
        }

        $definition->setArgument(0, $jobBatchMessageHandlerReferences);
    }
}
