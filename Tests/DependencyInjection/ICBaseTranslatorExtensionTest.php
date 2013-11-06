<?php
/**
 * @copyright 2013 Instaclick Inc.
 */

namespace IC\Bundle\Base\TranslatorBundle\Tests\DependencyInjection;

use IC\Bundle\Base\TranslatorBundle\DependencyInjection\ICBaseTranslatorExtension;
use IC\Bundle\Base\TestBundle\Test\DependencyInjection\ExtensionTestCase;

/**
 * Test for IC Base Translator Bundle
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 *
 * @author Enzo Rizzo <enzor@nationalfibre.net>
 */
class ICBaseTranslatorExtensionTest extends ExtensionTestCase
{
    /**
     * Test valid data
     *
     */
    public function testValidConfiguration()
    {
        $this->markTestIncomplete("Incomplete due to complexity");

        $loader = new ICBaseTranslatorExtension();
        $config =
            array(
                'translator' => array(
                    'fallback' => 'translator',
                ),
        );

        $this->load($loader, $config);
        //$this->assertParameter('en', 'ic_base_translator.service.translator');
    }
}
