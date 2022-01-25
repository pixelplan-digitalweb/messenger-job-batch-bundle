<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Pixelplan\MessengerJobBatchBundle\EventSubscriber\WorkerMessageSubscriber;
use Pixelplan\MessengerJobBatchBundle\JobBatch\JobBatchManager;
use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchMessageBus;
use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchMessageBusFactory;
use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchHandlerInterface;
use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchMiddleware;
use Pixelplan\MessengerJobBatchBundle\Storage\CacheStorage;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->instanceof(JobBatchHandlerInterface::class)
        ->tag('pixelplan_messenger_job_batch.job_batch_handler');

    $services
        ->set('pixelplan_messenger_job_batch.worker_message_subscriber', WorkerMessageSubscriber::class)
            ->args([
                tagged_iterator('pixelplan_messenger_job_batch.job_batch_handler'),
                service('pixelplan_messenger_job_batch.job_batch_manager')
            ])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            ->tag('kernel.event_subscriber')
            ->tag('monolog.logger', ['channel' => 'messenger'])

        ->set('pixelplan_messenger_job_batch.batch_middleware', JobBatchMiddleware::class)

        ->set('pixelplan_messenger_job_batch.cache_storage', CacheStorage::class)
            ->args([
                abstract_arg('Store')
            ])

        ->set('pixelplan_messenger_job_batch.job_batch_manager', JobBatchManager::class)
            ->args([
                service('pixelplan_messenger_job_batch.cache_storage'),
                abstract_arg('Lock Factory')
            ])
            ->call('setLogger', [service('logger')->ignoreOnInvalid()])
            ->tag('monolog.logger', ['channel' => 'messenger'])

        ->set('pixelplan_messenger_job_batch.batch_message_bus_factory', JobBatchMessageBusFactory::class)
            ->args([
                service('pixelplan_messenger_job_batch.job_batch_manager')
            ])

        ->set('cache.messenger_job_batch')
            ->parent('cache.app')
            ->tag('cache.pool')

        ->alias(JobBatchMessageBus::class, 'pixelplan_messenger_job_batch.batch_message_bus')
        ->alias(JobBatchManager::class, 'pixelplan_messenger_job_batch.job_batch_manager')
        ->alias(JobBatchMiddleware::class, 'pixelplan_messenger_job_batch.batch_middleware')
        ->alias(JobBatchMessageBusFactory::class, 'pixelplan_messenger_job_batch.batch_message_bus_factory')
    ;
};
