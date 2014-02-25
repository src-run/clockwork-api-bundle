<?php
/*
 * This file is part of the Scribe World Application.
 *
 * (c) Scribe Inc. <scribe@scribenet.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scribe\ClockworkBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader,
    Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * ScribeClockworkExtension
 */
class ScribeClockworkExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration(
            $configuration, 
            $configs
        );

        $container->setParameter(
            'scribe_clockwork.api_key',
            $config['api_key']
        );

        $container->setParameter(
            'scribe_clockwork.allow_long_messages',
            $config['allow_long_messages']
        );

        $container->setParameter(
            'scribe_clockwork.truncate_long_messages',
            $config['truncate_long_messages']
        );

        $container->setParameter(
            'scribe_clockwork.from_address',
            $config['from_address']
        );

        $container->setParameter(
            'scribe_clockwork.enable_ssl',
            $config['enable_ssl']
        );





        $loader = new YamlFileLoader(
            $container, 
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }
}
