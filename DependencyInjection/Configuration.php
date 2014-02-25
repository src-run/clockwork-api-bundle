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

use Symfony\Component\Config\Definition\Builder\TreeBuilder,
    Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('scribe_clockwork');

        $rootNode
            ->children()
                ->scalarNode('api_key')
                    ->defaultValue('undefined')
                    ->info('The key to connect to the Clockwork API')
                ->end()
                ->booleanNode('allow_long_messages')
                    ->defaultFalse()
                    ->info('Allow long SMS messages, multiple credit costs may apply')
                ->end()
                ->booleanNode('truncate_long_messages')
                    ->defaultTrue()
                    ->info('Truncate messages if text is too long - overwrites allow_long_messages if true')
                ->end()
                ->scalarNode('from_address')
                    ->defaultValue('ScribeClockworkBundle')
                    ->info('The from value used on sent SMS messages')
                ->end()
                ->booleanNode('enable_ssl')
                    ->defaultTrue()
                    ->info('Use SSL when making HTTP requests')
                ->end()
                ->enumNode('invalid_character_action')
                    ->defaultValue('replace_character')
                    ->values(['throw_error', 'remove_character', 'replace_character'])
                    ->info('Action to take if invalid characters are used within the SMS')
                ->end()
                ->booleanNode('log_activity')
                    ->defaultFalse()
                    ->info('Log activity using Monolog')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
