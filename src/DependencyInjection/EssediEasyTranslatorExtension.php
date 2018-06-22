<?php

namespace Essedi\EasyTranslation\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class EssediEasyTranslationExtension extends Extension
{

    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader(
                $container, new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.xml');
    }

}
