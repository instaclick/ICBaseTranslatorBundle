<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TranslatorBundle\Tests;

use IC\Bundle\Base\TranslatorBundle\ICBaseTranslatorBundle;

/**
 * Test for
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 *
 * @author Enzo Rizzo <enzor@nationalfibre.net>
 */

class ICBaseTranslatorBundleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the build method of the translation bundle
     */
    public function testBuild()
    {
        $container = $this->createMock('Symfony\Component\DependencyInjection\ContainerBuilder');
        $translator = new ICBaseTranslatorBundle();

        $translator->build($container);
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
