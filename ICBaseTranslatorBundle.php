<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\TranslatorBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use IC\Bundle\Base\TranslatorBundle\DependencyInjection\Compiler\TranslatorCompilerPass;

/**
 * Base translator bundle
 *
 * @author John Zhang <johnz@nationalfibre.net>
 * @author David Maignan <davidm@nationalfibre.net>
 */
class ICBaseTranslatorBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new TranslatorCompilerPass());
    }
}
