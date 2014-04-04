<?php

namespace IC\Bundle\Base\TranslatorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 */
class ICBaseTranslatorExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);
        $loader        = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loader->load('services.xml');

        $this->registerTranslatorConfiguration($config, $container);
    }

    /**
     * Loads the translator configuration.
     *
     * @param array            $config    A translator configuration array
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return null
     */
    private function registerTranslatorConfiguration(array $config, ContainerBuilder $container)
    {
        // Use the "real" translator instead of the identity default
        $translator = $container->findDefinition('ic_base_translator.service.translator');

        $translator->addMethodCall('setFallbackLocale', array($config['translator']['fallback']));

        // Discover translation directories
        $directoryList = array();
        $classList     = array(
            'Symfony\Component\Validator\Validator'                             => '/Resources/translations',
            'Symfony\Component\Form\Form'                                       => '/Resources/translations',
            'Symfony\Component\Security\Core\Exception\AuthenticationException' => '/../../Resources/translations',
        );

        foreach ($classList as $className => $relativePath) {
            if ( ! class_exists($className)) {
                continue;
            }

            $reflection      = new \ReflectionClass($className);
            $directoryList[] = dirname($reflection->getFilename()) . $relativePath;
        }

        $overridePath = $container->getParameter('kernel.root_dir') . '/Resources/%s/translations';

        foreach ($container->getParameter('kernel.bundles') as $bundle => $class) {
            $reflection = new \ReflectionClass($class);

            if (is_dir($dir = dirname($reflection->getFilename()) . '/Resources/translations')) {
                $directoryList[] = $dir;
            }

            if (is_dir($dir = sprintf($overridePath, $bundle))) {
                $directoryList[] = $dir;
            }
        }

        if (is_dir($dir = $container->getParameter('kernel.root_dir') . '/Resources/translations')) {
            $directoryList[] = $dir;
        }

        // Register translation resources
        if ( ! $directoryList) {
            return;
        }

        foreach ($directoryList as $dir) {
            $container->addResource(new DirectoryResource($dir));
        }

        $finder = Finder::create()
            ->files()
            ->filter(function (\SplFileInfo $file) {
                return 2 === substr_count($file->getBasename(), '.') && preg_match('/\.\w+$/', $file->getBasename());
            })
            ->in($directoryList)
        ;

        foreach ($finder as $file) {
            // filename is domain.locale.format
            list($domain, $locale, $format) = explode('.', $file->getBasename(), 3);

            $translator->addMethodCall('addResource', array($format, (string) $file, $locale, $domain));
        }
    }
}
