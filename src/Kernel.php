<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    public function process(ContainerBuilder $container): void
    {
        if('test' === $this->environment) {
            //Prevents Doctrine entities/connections from being reset in between requests
            //from the test client in a single test
            $container->getDefinition('doctrine')->clearTag('kernel.reset');
        }
    }
}
