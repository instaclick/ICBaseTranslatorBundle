<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TranslatorBundle\Tests\DependencyInjection\Compiler;

use IC\Bundle\Base\TranslatorBundle\DependencyInjection\Compiler\TranslatorCompilerPass;

/**
 *  Test
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 *
 * @author Enzo Rizzo <enzor@nationalfibre.net>
 */
class TranslatorCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \IC\Bundle\Base\TranslatorBundle\DependencyInjection\Compiler
     */
    private $compiler;

    /**
     * Create the setup for the test
     */
    protected function setUp()
    {
        parent::setUp();

        $this->compiler = new TranslatorCompilerPass();
    }

    /**
     * Test for the early exit in case the default translator is not defined in the container
     */
    public function testProcessEarlyExit()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('translator.default')
            ->will($this->returnValue(false));

        $this->compiler->process($container);
    }

    /**
     * Test with no legacy alias. Early exit from foreach
     */
    public function testProcessWithException()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container
            ->expects($this->any())
            ->method('hasDefinition')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $attributes = array (
            'id'  => array(
                0 => array(
                    'alias' => true,
                    'legacy-alias'=> null
                )
            )
        );

        $container
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('translation.loader')
            ->will($this->returnValue($attributes));

        $definition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $definition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('translation.loader')
            ->will($this->returnValue($definition));

        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with('translator.default')
            ->will($this->returnValue($definition));

        $this->compiler->process($container);
    }

    /**
     * Test process with normal scenario
     */
    public function testProcess()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');

        $container
            ->expects($this->any())
            ->method('hasDefinition')
            ->with($this->anything())
            ->will($this->onConsecutiveCalls(true, true));

        $attributes = array (
                'id'  => array(0 => array('alias' => true)),
                'id2' => array(0 => array('alias' => false, 'legacy-alias' => true))
        );

        $container
            ->expects($this->any())
            ->method('findTaggedServiceIds')
            ->with('translation.loader')
            ->will($this->returnValue($attributes));

        $definition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $definition
            ->expects($this->once())
            ->method('replaceArgument')
            ->with($this->anything())
            ->will($this->returnValue(true));

        $container
            ->expects($this->once())
            ->method('getDefinition')
            ->with('translation.loader')
            ->will($this->returnValue($definition));

        $container
            ->expects($this->once())
            ->method('findDefinition')
            ->with('translator.default')
            ->will($this->returnValue($definition));

        $this->compiler->process($container);
    }

    /**
     * Create a mock object of a given class name.
     *
     * @param string $class Class name
     *
     * @return mixed
     */
    public function createMock($class)
    {
        return $this
            ->getMockBuilder($class)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
