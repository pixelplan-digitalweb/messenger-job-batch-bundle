<?php

declare(strict_types=1);

namespace Pixelplan\MessengerJobBatchBundle;

use Pixelplan\MessengerJobBatchBundle\DependencyInjection\MessengerJobBatchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class MessengerJobBatchBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new MessengerJobBatchCompilerPass());
    }

//    public function getContainerExtension(): ExtensionInterface
//    {
//        if (null === $this->extension) {
//            $this->extension = new MessengerJobBatchExtension();
//        }
//        return $this->extension;
//    }
}
