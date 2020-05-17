<?php

namespace ArturDoruch\SimpleRestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Artur Doruch <arturdoruch@interia.pl>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('artur_doruch_simple_rest');

        $rootNode
            ->children()
                ->arrayNode('api_paths')
                    ->isRequired()
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('form_error_flatten_messages')
                    ->info('Whether to flatten error messages multidimensional array into simple array with key-value pairs.')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
