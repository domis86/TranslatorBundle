<?php

namespace Domis86\TranslatorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Domis86\TranslatorBundle\DependencyInjection\Extension;
use Domis86\TranslatorBundle\DependencyInjection\Compiler\TranslatorCompilerPass;

class Domis86TranslatorBundle extends Bundle
{
    /**
     * @inheritdoc
     */
    public function getContainerExtension()
    {
        return new Extension();
    }

    /**
     * @inheritdoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TranslatorCompilerPass());
        parent::build($container);
    }
}
