<?php

namespace ArturDoruch\SimpleRestBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class ArturDoruchSimpleRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('arturdoruch_simple_rest.api_paths', $config['api_paths']);
        $container->setParameter('arturdoruch_simple_rest.form_error_flatten_messages', $config['form_error_flatten_messages']);
    }
}
