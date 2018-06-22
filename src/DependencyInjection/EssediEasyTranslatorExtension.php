<?php

namespace Essedi\EasyTranslator\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EssediEasyTranslatorExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
                $container, new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.xml');
    }

}
