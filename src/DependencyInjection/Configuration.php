<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('messenger_job_batch');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->info('Messenger Job Batch configuration')
            ->children()
                ->scalarNode('lock_factory')
                    ->info('The service ID of the lock factory used by this job batch processor')
                    ->defaultValue('lock.factory')
                ->end()
                ->scalarNode('cache_pool')
                    ->info('The cache pool to use for storing the current job batch state')
                    ->defaultValue('cache.messenger_job_batch')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
