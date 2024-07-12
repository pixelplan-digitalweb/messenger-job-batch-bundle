<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\DependencyInjection;

use Pixelplan\MessengerJobBatchBundle\Messenger\JobBatchHandlerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

final class MessengerJobBatchExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $configuration = $this->getConfiguration($configs, $container);

        if($configuration === null) {
            return;
        }

        $config = $this->processConfiguration($configuration, $configs);

        $definition = $container->getDefinition('pixelplan_messenger_job_batch.cache_storage');
        $definition->setArgument(0, new Reference($config['cache_pool']));

        $definition = $container->getDefinition('pixelplan_messenger_job_batch.job_batch_manager');
        $definition->setArgument(1, new Reference($config['lock_factory']));

        $container->registerForAutoconfiguration(JobBatchHandlerInterface::class)
            ->addTag('pixelplan_messenger_job_batch.job_batch_handler');
    }

    public function getAlias(): string
    {
        return 'messenger_job_batch';
    }
}
