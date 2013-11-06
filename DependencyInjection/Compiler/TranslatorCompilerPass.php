<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\TranslatorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Base translator bundle Compiler pass
 *
 * @author John Zhang <johnz@nationalfibre.net>
 * @author David Maignan <davidm@nationalfibre.net>
 */
class TranslatorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ( ! $container->hasDefinition('translator.default')) {
            return;
        }

        $container->removeDefinition('translator.default');
        $container->setAlias('translator.default', 'ic_base_translator.service.translator');

        $loaderList = array();

        foreach ($container->findTaggedServiceIds('translation.loader') as $id => $attributes) {
            $loaderList[$id][] = $attributes[0]['alias'];

            if ( ! isset($attributes[0]['legacy-alias'])) {
                continue;
            }

            $loaderList[$id][] = $attributes[0]['legacy-alias'];
        }

        if ($container->hasDefinition('translation.loader')) {
            $definition = $container->getDefinition('translation.loader');

            $this->addLoaderListToDefinition($definition, $loaderList);
        }

        $container->findDefinition('translator.default')->replaceArgument(2, $loaderList);
    }

    /**
     * Add list of Loaders to Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param array                                             $loaderList
     */
    private function addLoaderListToDefinition(Definition $definition, array $loaderList)
    {
        foreach ($loaderList as $id => $formatList) {
            $this->addFormatListToDefinition($definition, new Reference($id), $formatList);
        }
    }

    /**
     * Add list of Formats associated with a Reference to Definition.
     *
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param \Symfony\Component\DependencyInjection\Reference  $reference
     * @param array                                             $formatList
     */
    private function addFormatListToDefinition(Definition $definition, Reference $reference, array $formatList)
    {
        foreach ($formatList as $format) {
            $definition->addMethodCall('addLoader', array($format, $reference));
        }
    }
}
