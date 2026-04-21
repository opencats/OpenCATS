<?php

namespace OpenCATS\Tests\Behat\ServiceContainer\Driver;

use Behat\MinkExtension\ServiceContainer\Driver\DriverFactory;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\Definition;

class BrowserKitFactory implements DriverFactory
{
    public function getDriverName()
    {
        return 'browserkit';
    }

    public function supportsJavascript()
    {
        return false;
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->arrayNode('server_parameters')
                    ->useAttributeAsKey('key')
                    ->prototype('variable')->end()
                ->end()
            ->end();
    }

    public function buildDriver(array $config)
    {
        if (!class_exists('Behat\\Mink\\Driver\\BrowserKitDriver')) {
            throw new \RuntimeException('Install MinkBrowserKitDriver in order to use browserkit driver.');
        }

        $clientDefinition = new Definition('OpenCATS\\Tests\\Behat\\BrowserKit\\StreamHttpBrowser', array(
            $config['server_parameters'],
        ));

        return new Definition('Behat\\Mink\\Driver\\BrowserKitDriver', array(
            $clientDefinition,
        ));
    }
}
