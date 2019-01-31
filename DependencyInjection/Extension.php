<?php

namespace Domis86\TranslatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension as BaseExtension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages bundle configuration
 */
class Extension extends BaseExtension
{
    /** @var string */
    private $bundlePath = '';

    public function __construct($bundlePath)
    {
        $this->bundlePath = $bundlePath;
    }

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

        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->bundlePath . DIRECTORY_SEPARATOR .'Resources' . DIRECTORY_SEPARATOR . 'config'));
        $loader->load('services.yml');
        if ($container->getParameter('kernel.environment') == 'dev') {
            // load configs for dev environment (toolbar for translation messages, admin etc)
            $loader->load('services_dev.yml');
        }

        $is_enabled = $config['is_enabled'];
        $is_web_debug_dialog_enabled = $is_enabled ? $config['is_web_debug_dialog_enabled'] : false;

        if ($is_enabled && empty($config['managed_locales'])) {
            throw new InvalidConfigurationException("You must configure 'managed_locales' if '{$this->getAlias()}' is enabled");
        }

        $container->setParameter($this->getAlias() . '.is_enabled', $is_enabled);
        $container->setParameter($this->getAlias() . '.is_web_debug_dialog_enabled', $is_web_debug_dialog_enabled);
        $container->setParameter($this->getAlias() . '.managed_locales', $config['managed_locales']);
        $container->setParameter($this->getAlias() . '.whitelisted_controllers_regexes', $config['whitelisted_controllers_regexes']);
        $container->setParameter($this->getAlias() . '.ignored_controllers_regexes', $config['ignored_controllers_regexes']);
        $container->setParameter($this->getAlias() . '.ignored_domains', $config['ignored_domains']);
        $container->setParameter($this->getAlias() . '.assets_base_path', $config['assets_base_path']);
        $container->setParameter($this->getAlias() . '.assets', $config['assets']);
        $container->setParameter($this->getAlias() . '.config', $config);

        if ($is_enabled) {
            $container->getDefinition('domis86_translator.controller_listener')
                ->setPublic(true)
                ->addTag('kernel.event_listener', array(
                        'event' => 'kernel.controller',
                        'priority' => 16
                    )
                );

            $container->getDefinition('domis86_translator.response_listener')
                ->setPublic(true)
                ->addTag('kernel.event_listener', array(
                        'event' => 'kernel.response',
                        'priority' => -64
                    )
                );

            $container->getDefinition('domis86_translator.command_listener')
                ->setPublic(true)
                ->addTag('kernel.event_listener', array(
                        'event' => 'console.command',
                        'method' => 'onConsoleCommand',
                        'priority' => 16
                    )
                )
                ->addTag('kernel.event_listener', array(
                        'event' => 'console.terminate',
                        'method' => 'onConsoleTerminate',
                        'priority' => -64
                    )
                );
        }

        if ($is_web_debug_dialog_enabled) {
            $container->getDefinition('domis86_translator.response_listener')
                ->addMethodCall(
                    'enableWebDebugDialog', array()
                );

            $dataCollectorDefinition = new Definition();
            $dataCollectorDefinition->setClass($container->getParameter('domis86_translator.data_collector.class'));
            $dataCollectorDefinition->addArgument(new Reference('domis86_translator.message_manager'));
            $dataCollectorDefinition->addTag('data_collector', array(
                'template' => 'Domis86TranslatorBundle:DataCollector:translatorDataCollector',
                'id' => 'domis86_translator_data_collector'
            ));
            $container->setDefinition('domis86_translator.data_collector', $dataCollectorDefinition);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($this->getAlias());
    }
}
