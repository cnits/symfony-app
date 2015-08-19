<?php

namespace CnitSnappyPDFBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $fixOptionKeys = function ($options) {
            $fixedOptions = array();
            foreach ($options as $key => $value) {
                $fixedOptions[str_replace('_', '-', $key)] = $value;
            }
            return $fixedOptions;
        };

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cnit_snappy_pdf');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        $rootNode -> children()
            -> scalarNode("temporary_folder") -> end() -> end();
        $rootNode -> children()
            -> arrayNode("pdf")
                -> addDefaultsIfNotSet()
                -> children()
                    -> booleanNode("enable") -> end()
                    -> scalarNode("binary") -> defaultValue("wkhtmltopdf") -> end()
                    ->arrayNode('options')
                        ->performNoDeepMerging()
                        ->useAttributeAsKey('name')
                        ->beforeNormalization()
                            ->always($fixOptionKeys) -> end()
                        ->prototype('scalar') -> end()
                    -> end()
                    -> arrayNode('env')
                        -> prototype('scalar') -> end()
                    -> end()
                -> end()
            -> end()
            -> arrayNode('image')
                -> addDefaultsIfNotSet()
                -> children()
                    -> booleanNode('enabled') -> defaultTrue() -> end()
                    -> scalarNode('binary') -> defaultValue('wkhtmltoimage') -> end()
                    -> arrayNode('options')
                        -> performNoDeepMerging()
                        -> useAttributeAsKey('name')
                        -> beforeNormalization()
                            -> always($fixOptionKeys)
                        -> end()
                        -> prototype('scalar') -> end()
                    -> end()
                    -> arrayNode('env')
                        -> prototype('scalar')->end()
                    -> end()
                ->end()
            ->end()
        ->end();
        return $treeBuilder;
    }
}
