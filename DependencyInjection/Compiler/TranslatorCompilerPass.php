<?php
namespace Domis86\TranslatorBundle\DependencyInjection\Compiler;

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

        // Set domis86_translator.translator.parent service which will be injected to domis86_translator.translator
        $originalTranslatorDefinition = $container->getDefinition('translator.default');
        $originalTranslatorDefinition->setPublic(false);
        $container->setDefinition('domis86_translator.translator.parent', $originalTranslatorDefinition);

        $container->setAlias('translator',         'domis86_translator.translator');
        $container->setAlias('translator.default', 'domis86_translator.translator'); // this override is needed because in some places @translator.default is injected instead of @translator - like in @validator service, see: vendor/symfony/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/config/validator.xml

        $parentTranslatorReference = new Reference('domis86_translator.translator.parent');
        $container->getDefinition('domis86_translator.translator')->addMethodCall(
            'setParentTranslator', array($parentTranslatorReference)
        );
        $container->getDefinition('domis86_translator.web_debug_dialog')->addMethodCall(
            'setParentTranslator', array($parentTranslatorReference)
        );
    }
}
