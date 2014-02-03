<?php

namespace Domis86\TranslatorBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\ConfigurationInterface;


/**
 * Configuration
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @var string
     */
    private $alias;

    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->alias);

        $basePathParam = '%domis86_translator.assets_base_path%';

        /** @noinspection PhpUndefinedMethodInspection */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('is_enabled')->defaultFalse()->end()
                ->booleanNode('is_web_debug_dialog_enabled')->defaultFalse()->end()
                ->arrayNode('managed_locales')
                    ->prototype('scalar')->end()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('assets_base_path')
                    ->cannotBeEmpty()
                    ->defaultValue('/bundles/domis86translator/')
                ->end()
                ->arrayNode('assets')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->append($this->addAssetsChildNode('jquery', array(
                            $basePathParam . 'external/js/jquery-2.0.3.min.js'
                        )))
                        ->append($this->addAssetsChildNode('jquery-ui', array(
                            $basePathParam . 'external/css/jquery-ui.1.10.3.css',
                            $basePathParam . 'external/js/jquery-ui.1.10.3.min.js'
                        )))
                        ->append($this->addAssetsChildNode('datatables', array(
                            $basePathParam . 'external/css/jquery.dataTables.css',
                            $basePathParam . 'external/js/jquery.dataTables.1.10.0-dev.min.js'
                        )))
                        ->append($this->addAssetsChildNode('jeditable', array(
                            $basePathParam . 'external/js/jquery.jeditable.mini.js'
                        )))
                        ->append($this->addAssetsChildNode('domis86_loadwebdebugdialog', array(
                            $basePathParam . 'js/loadWebDebugDialog.js'
                        )))
                        ->append($this->addAssetsChildNode('domis86_webdebugdialog', array(
                            $basePathParam . 'js/webDebugDialog.js'
                        )))
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @param string $name
     * @param array $defaultItems
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function addAssetsChildNode($name, array $defaultItems)
    {
        $builder = new TreeBuilder();
        $node = $builder->root($name);

        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->prototype('scalar')->end()
            ->cannotBeEmpty()
            ->defaultValue($defaultItems)
        ;

        return $node;
    }
}
