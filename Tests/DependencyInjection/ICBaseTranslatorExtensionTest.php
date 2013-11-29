<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TranslatorBundle\Tests\DependencyInjection;

use IC\Bundle\Base\TranslatorBundle\DependencyInjection\ICBaseTranslatorExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Test for IC Base Translator Bundle
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 *
 * @author Enzo Rizzo <enzor@nationalfibre.net>
 */
class ICBaseTranslatorExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->container);

        parent::tearDown();
    }

    /**
     * Test valid configuration
     *
     * @param array $config
     *
     * @dataProvider provideValidData
     */
    public function testValidConfiguration($config)
    {
        $this->container->setParameter('kernel.root_dir', 'mock_root_dir');
        $this->container->setParameter('kernel.bundles', array());

        $loader = new ICBaseTranslatorExtension();

        $this->load($loader, $config);

        $translator = $this->container->getDefinition('ic_base_translator.service.translator');

        $this->assertHasDefinition('ic_base_translator.service.translator');
        $this->assertDICDefinitionMethodCallAt(0, $translator, 'setFallbackLocale', array('en'));
    }

    /**
     * Provide configuration data
     *
     * @return array
     */
    public function provideValidData()
    {
        return array(
            array(
                array(
                    'translator' => array(
                        'fallback' => 'en',
                    )
                )
            )
        );
    }

    /**
     * Retrieve the container
     *
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Loads the configuration into a provided Extension.
     *
     * @param \Symfony\Component\HttpKernel\DependencyInjection\Extension $extension
     * @param array                                                       $configuration
     */
    protected function load(Extension $extension, array $configuration)
    {
        $extension->load(array($configuration), $this->container);
    }

    /**
     * Assertion on the Definition existance of a Container Builder.
     *
     * @param string $id
     */
    protected function assertHasDefinition($id)
    {
        $actual = $this->container->hasDefinition($id) ?: $this->containerBuilder->hasAlias($id);

        $this->assertTrue($actual);
    }

    /**
     * Assertion on the called Method position of a DIC Service Definition.
     *
     * @param integer                                           $position
     * @param \Symfony\Component\DependencyInjection\Definition $definition
     * @param string                                            $methodName
     * @param array                                             $params
     */
    protected function assertDICDefinitionMethodCallAt($position, Definition $definition, $methodName, array $params = null)
    {
        $calls = $definition->getMethodCalls();

        if ( ! isset($calls[$position][0])) {
            // Throws an Exception
            $this->fail(
                sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
            );
        }

        $this->assertEquals(
            $methodName,
            $calls[$position][0],
            sprintf('Method "%s" is expected to be called at position %s.', $methodName, $position)
        );

        if ($params !== null) {
            $this->assertEquals(
                $params,
                $calls[$position][1],
                sprintf('Expected parameters to methods "%s" do not match the actual parameters.', $methodName)
            );
        }
    }
}
