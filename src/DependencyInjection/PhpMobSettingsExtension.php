<?php

/*
 * This file is part of the PhpMob package.
 *
 * (c) Ishmael Doss <nukboon@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpMob\SettingsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Ishmael Doss <nukboon@gmail.com>
 */
class PhpMobSettingsExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return 'phpmob_settings';
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $schemaRegistryDef = $container->getDefinition('phpmob.settings.schema_registry');

        foreach ($config['schemas'] as $section => $data) {
            $schemaRegistryDef->addMethodCall('add', [$section, $data]);
        }

        if ($config['cache']['service']) {
            $cachedDefinition = $container->getDefinition('phpmob.settings.cached_manager');
            $cachedDefinition->setAbstract(false);
            $cachedDefinition->setLazy(true);
            $cachedDefinition->setDecoratedService('phpmob.settings.manager');
            $cachedDefinition->setArgument(0, new Reference('phpmob.settings.cached_manager.inner'));
            $cachedDefinition->setArgument(1, new Reference($config['cache']['service']));
            $cachedDefinition->setArgument(2, $config['cache']['lifetime']);
        }
    }
}
