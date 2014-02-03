<?php
namespace Domis86\TranslatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TranslatorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('domis86_translator.translator')) {
            return;
        }

        if (!$container->getParameter('domis86_translator.is_enabled')) {
            return;
        }

        // Set domis86_translator.translator.parent service.
        // It will be injected to domis86_translator.translator service (see services.yml).

        if ($container->hasAlias('translator')) {
            // original translator is an alias.
            $originalTranslatorAlias = new Alias((string)$container->getAlias('translator'), false);
            $container->setAlias('domis86_translator.translator.parent', $originalTranslatorAlias);
        } else {
            // original translator is a definition.
            $originalTranslatorDefinition = $container->getDefinition('translator');
            $originalTranslatorDefinition->setPublic(false);
            $container->setDefinition('domis86_translator.translator.parent', $originalTranslatorDefinition);
        }
        $container->setAlias('translator', 'domis86_translator.translator');

        $parentTranslatorReference = new Reference('domis86_translator.translator.parent');
        $container->getDefinition('domis86_translator.translator')->addMethodCall(
            'setParentTranslator', array($parentTranslatorReference)
        );
        $container->getDefinition('domis86_translator.web_debug_dialog')->addMethodCall(
            'setParentTranslator', array($parentTranslatorReference)
        );
    }
}
