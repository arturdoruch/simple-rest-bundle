<?php

namespace ArturDoruch\SimpleRestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ArturDoruchSimpleRestExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('arturdoruch_simple_rest.api_paths', $config['api_paths']);
        $container->setParameter('arturdoruch_simple_rest.form_error_flatten_messages', $config['form_error_flatten_messages']);
    }
}
