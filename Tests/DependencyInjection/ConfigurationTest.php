<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TranslatorBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use IC\Bundle\Base\TranslatorBundle\DependencyInjection\Configuration;

/**
 * Test for configuration
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 * @group DependencyInjection
 *
 * @author Enzo Rizzo <enzor@nationalfibre.net>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Symfony\Component\Config\Definition\Processor
     */
    protected $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->processor = new Processor();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->processor);

        parent::tearDown();
    }

    /**
     * Processes an array of raw configuration and returns a compiled version.
     *
     * @param \Symfony\Component\Config\Definition\ConfigurationInterface $configuration
     * @param array                                                       $rawConfiguration
     *
     * @return array A normalized array
     */
    public function processConfiguration(ConfigurationInterface $configuration, array $rawConfiguration)
    {
        return $this->processor->processConfiguration($configuration, $rawConfiguration);
    }

    /**
     * Test For Valid Data
     */
    public function testForValidData()
    {
        $data          = $this->provideValidData();
        $configuration = $this->processConfiguration(new Configuration(), $data);

        $this->assertEquals('en', $configuration['translator']['fallback']);
        $this->assertTrue($configuration['translator']['enabled']);
    }

    /**
     * Provide Valid Configuration Data
     *
     * @return array
     */
    public function provideValidData()
    {
        return array(
            array(
                'translator' => array(
                    'fallback' => 'en',
                    'enabled' => true,
                ),
            ),
        );
    }
}
