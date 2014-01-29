<?php

namespace Domis86\TranslatorBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration
 */
class Extension extends BaseExtension
{
    /**
     * @return string Bundle alias
     */
    public function getAlias()
    {
        return 'domis86_translator';
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        if ($container->getParameter('kernel.environment') == 'dev') {
            // load configs for dev environment (toolbar for translation messages, admin etc)
            $loader->load('services_dev.yml');
        }

        $container->setParameter($this->getAlias() . '.config', $config);
        $container->setParameter($this->getAlias() . '.managed_locales', $config['managed_locales']);
        $container->setParameter($this->getAlias() . '.assets_base_path', $config['assets_base_path']);
        $container->setParameter($this->getAlias() . '.assets', $config['assets']);
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }
}
