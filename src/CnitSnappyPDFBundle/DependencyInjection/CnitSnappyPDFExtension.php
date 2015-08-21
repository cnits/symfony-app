<?php

namespace CnitSnappyPDFBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CnitSnappyPDFExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$loader->load('services.yml');

        if ($config['pdf']['enabled']) {
            $loader->load('pdf.xml');
            $container->setParameter('cnit.pdf.binary', $config['pdf']['binary']);
            $container->setParameter('cnit.pdf.options', $config['pdf']['options']);
            $container->setParameter('cnit.pdf.env', $config['pdf']['env']);
            if (!empty($config['temporary_folder'])) {
                $container->findDefinition('cnit.pdf.internal_generator')
                    ->addMethodCall('setTemporaryFolder', array($config['temporary_folder']));
            }
        }
        if ($config['image']['enabled']) {
            $loader->load('image.xml');
            $container->setParameter('cnit.image.binary', $config['image']['binary']);
            $container->setParameter('cnit.image.options', $config['image']['options']);
            $container->setParameter('cnit.image.env', $config['image']['env']);
            if (!empty($config['temporary_folder'])) {
                $container->findDefinition('cnit.image.internal_generator')
                    ->addMethodCall('setTemporaryFolder', array($config['temporary_folder']));
            }
        }
    }
}
