<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\TranslatorBundle\Tests\Translation;

use IC\Bundle\Base\TranslatorBundle\Translation\Translator;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Translation\MessageSelector;

/**
 * Class TranslatorTest
 *
 * @group ICBaseTranslatorBundle
 * @group Unit
 *
 * @author David Maignan <davidm@nationalfibre.net>
 * @author John Zhang <johnz@nationalfibre.net>
 */
class TranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $tmpDir;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->tmpDir = sys_get_temp_dir().'/sf2_translation';
        $this->deleteTmpDir();
    }

    /**
     * Tear Down
     */
    public function tearDown()
    {
        $this->deleteTmpDir();
    }

    /**
     * Delete tmp directory
     */
    protected function deleteTmpDir()
    {
        if ( ! file_exists($dir = $this->tmpDir)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    /**
     * Test trans method without caching
     */
    public function testTransWithoutCaching()
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
    }

    /**
     * Test trans method with caching
     */
    public function testTransWithCaching()
    {
        // prime the cache
        $translator = $this->getTranslator($this->getLoader(), array('cache_dir' => $this->tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));

        // do it another time as the cache is primed now
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $translator = $this->getTranslator($loader, array('cache_dir' => $this->tmpDir));
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $this->assertEquals('foo (FR)', $translator->trans('foo'));
        $this->assertEquals('bar (EN)', $translator->trans('bar'));
        $this->assertEquals('foobar (ES)', $translator->trans('foobar'));
        $this->assertEquals('choice 0 (EN)', $translator->transChoice('choice', 0));
        $this->assertEquals('no translation', $translator->trans('no translation'));
        $this->assertEquals('foobarfoo (PT-PT)', $translator->trans('foobarfoo'));
        $this->assertEquals('other choice 1 (PT-BR)', $translator->transChoice('other choice', 1));
    }

    /**
     * Test trans method with MessageFormatter
     *
     * @param string $valueToConvert
     * @param string $expected
     * @param array  $parameters
     * @param float  $phpVersion
     *
     * @dataProvider transMessageFormatterProvider
     */
    public function testTransMessageFormatter($valueToConvert, $expected, $parameters, $phpVersion)
    {
        date_default_timezone_set('UTC');

        $currentVersion = (float) substr(PHP_VERSION, 0, 3);

        if ($currentVersion < $phpVersion) {
            $this->markTestSkipped('Required PHP >= ' . $phpVersion);
        }

        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $this->assertEquals($expected, $translator->trans($valueToConvert, $parameters));
    }

    /**
     * Dataprovider for trans method with MessageFormatter
     *
     * @return array
     */
    public function transMessageFormatterProvider()
    {
        // To make this test time-insensitive.
        $dateInTorontoTimezone = new \DateTime('1998-03-23 16:34:20 -0500');
        $dateInUTCTimezone     = new \DateTime('1998-03-23 16:34:20 +0000');

        return array(
            array('mock test one', 'mock test one', array(), 5.3),
            array('%name% {is cute.', 'panda {is cute.', array('%name%' => 'panda'), 5.3),
            array('{0, select, male {His name} other {Her name}}', 'His name', array('male'), 5.3),
            array(
                '{0, select, male {His name} female {Her name} other {Their names}}',
                'Their names',
                array('mock value'),
                5.3
            ),
            array(
                'Peter has {0,choice,0#{0,number} cat|1#{0,number} cat|1<{0,number} cats} and {1,choice,0#{1,number} dog| 1#{1,number} dog| 1<{1,number} dogs}',
                'Peter has 0 cat and 2 dogs',
                array(0, 2),
                5.5
            ),
            array(
                'Hello %name%',
                'Hello Fabien',
                array('%name%'=>'Fabien'),
                5.3
            ),
            array(
                '{gender_of_host, select,
                      female {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to her party.}
                          =2 {{host} invites {guest} and one other person to her party.}
                         other {{host} invites {guest} and # other people to her party.}}}
                      male {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to his party.}
                          =2 {{host} invites {guest} and one other person to his party.}
                         other {{host} invites {guest} and # other people to his party.}}}
                      other {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to their party.}
                          =2 {{host} invites {guest} and one other person to their party.}
                          other {{host} invites {guest} and # other people to their party.}}}}',
                'Foo invites Bar and one other person to his party.',
                array('gender_of_host' => 'male', 'num_guests' => 2, 'host' => 'Foo', 'guest' => 'Bar'),
                5.5
            ),
            array(
                'My name is {0}, born in %year%',
                'My name is Juti, born in 1990',
                array('Juti', '%year%' => '1990'),
                5.3
            ),
            array(
                'My name is {name}, born in %year%',
                'My name is Juti, born in %year%',
                array('name' => 'Juti', '%year%' => '1980'),
                5.5
            ),
            array(
                'At {1,time} on {1,date}, there was {2} on planet {0,number,integer}.',
                'At 21:34:20 on 23/03/1998, there was a disturbance in the Force on planet 7.',
                array(7, $dateInTorontoTimezone->getTimestamp() , 'a disturbance in the Force'),
                5.3
            ),
            array(
                'At {1,time} on {1,date}, there was {2} on planet {0,number,integer}.',
                'At 16:34:20 on 23/03/1998, there was a disturbance in the Force on planet 7.',
                array(7, $dateInUTCTimezone->getTimestamp(), 'a disturbance in the Force'),
                5.3
            ),
        );
    }

    /**
     * Test trans choice method with MessageFormatter
     *
     * @param string  $valueToConvert
     * @param string  $expected
     * @param integer $number
     * @param array   $parameters
     * @param float   $phpVersion
     *
     * @dataProvider transChoiceMessageFormatterProvider
     */
    public function testTransChoiceMessageFormatter($valueToConvert, $expected, $number, $parameters, $phpVersion)
    {
        $currentVersion = (float) substr(PHP_VERSION, 0, 3);

        if ($currentVersion < $phpVersion) {
            return;
        }

        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $this->assertEquals($expected, $translator->transChoice($valueToConvert, $number, $parameters));
    }

    /**
     * Dataprovider for trans method with MessageFormatter
     *
     * @return array
     */
    public function transChoiceMessageFormatterProvider()
    {
        return array(
            array('{12322} test singular | {1} test plural', 'test plural', 1, array(), 5.3),
            array('[0,10] test singular | {11} test plural', 'test singular', 10, array(), 5.3),
            array('Peter has {0,choice,0#{0,number} cat|1#{0,number} cat|1<{0,number} cats} and {1,choice,0#{1,number} dog| 1#{1,number} dog| 1<{1,number} dogs}', 'Peter has 12 cats and 1 dog', 12, array(1), 5.5),
            array('[0,10] test singular %param% | {11} test plural %param%', 'test singular mock param', 10, array('%param%' => 'mock param'), 5.3),
            array(']0,10] test singular | {11} test plural', 'test singular', 10, array(), 5.3),
        );
    }

    /**
     * Test pattern compatibly with legacy code
     *
     * @param string  $valueToTest
     * @param boolean $expected
     *
     * @dataProvider isCompatibleWithMessageFormatterProvider
     */
    public function testIsCompatibleWithMessageFormatter($valueToTest, $expected)
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $translator->trans('mock id', array());

        $this->assertEquals($expected, $translator->isCompatibleWithMessageFormatter($valueToTest));
    }

    /**
     * Data provider for isCompatibleWithMessageFormatter method
     *
     * @return array
     */
    public function isCompatibleWithMessageFormatterProvider()
    {
        return array(
            array('{0} foo', true),
            array('{0, 1, 2, 3} foo', true),
            array('[1,2] first | [3,4] second', false),
            array(']1,2] first | [3,4] second', false),
            array(']1,2[ first | [3,4] second', false),
            array('[1,2[ first | [3,4] second', false),
            array('{1} first | [3,4] second', false),
            array('Peter has {0,choice,0#{0,number} cat|1#{0,number} cat|1<{0,number} cats} and {1,choice,0#{1,number} dog| 1#{1,number} dog| 1<{1,number} dogs}', true),
            array('{gender_of_host, select,
                      female {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to her party.}
                          =2 {{host} invites {guest} and one other person to her party.}
                         other {{host} invites {guest} and # other people to her party.}}}
                      male {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to his party.}
                          =2 {{host} invites {guest} and one other person to his party.}
                         other {{host} invites {guest} and # other people to his party.}}}
                      other {{num_guests, plural, offset:1
                          =0 {{host} does not give a party.}
                          =1 {{host} invites {guest} to their party.}
                          =2 {{host} invites {guest} and one other person to their party.}
                          other {{host} invites {guest} and # other people to their party.}}}}', true),
            array('At {1,time} on {1,date}, there was {2} on planet {0,number,integer}.', true)
        );
    }

    /**
     * Test getLocale method
     */
    public function testGetLocale()
    {
        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $request
            ->expects($this->once())
            ->method('getLocale')
            ->will($this->returnValue('en'))
        ;

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');

        $container
            ->expects($this->exactly(2))
            ->method('isScopeActive')
            ->with('request')
            ->will($this->onConsecutiveCalls(false, true))
        ;

        $container
            ->expects($this->once())
            ->method('has')
            ->with('request')
            ->will($this->returnValue(true))
        ;

        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue($request))
        ;

        $translator = new Translator($container, new MessageSelector());

        $this->assertNull($translator->getLocale());
        $this->assertSame('en', $translator->getLocale());
    }

    /**
     * Get Catalogue
     *
     * @param string $locale
     * @param array  $messages
     *
     * @return MessageCatalogue
     */
    protected function getCatalogue($locale, $messages)
    {
        $catalogue = new MessageCatalogue($locale);
        foreach ($messages as $key => $translation) {
            $catalogue->set($key, $translation);
        }

        return $catalogue;
    }

    /**
     * Get Loader
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLoader()
    {
        $loader = $this->getMock('Symfony\Component\Translation\Loader\LoaderInterface');
        $loader
            ->expects($this->at(0))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('fr', array(
                'foo' => 'foo (FR)',
            ))))
        ;
        $loader
            ->expects($this->at(1))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('en', array(
                'foo'    => 'foo (EN)',
                'bar'    => 'bar (EN)',
                'choice' => '{0} choice 0 (EN)|{1} choice 1 (EN)|]1,Inf] choice inf (EN)',
            ))))
        ;
        $loader
            ->expects($this->at(2))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('es', array(
                'foobar' => 'foobar (ES)',
            ))))
        ;
        $loader
            ->expects($this->at(3))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('pt-PT', array(
                'foobarfoo' => 'foobarfoo (PT-PT)',
            ))))
        ;
        $loader
            ->expects($this->at(4))
            ->method('load')
            ->will($this->returnValue($this->getCatalogue('pt_BR', array(
                'other choice' => '{0} other choice 0 (PT-BR)|{1} other choice 1 (PT-BR)|]1,Inf] other choice inf (PT-BR)',
            ))))
        ;

        return $loader;
    }

    /**
     * Get Container
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $loader
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getContainer($loader)
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($loader))
        ;

        return $container;
    }

    /**
     * Get Translator
     *
     * @param \PHPUnit_Framework_MockObject_MockObject $loader
     * @param array                                    $options
     *
     * @return Translator
     */
    public function getTranslator($loader, $options = array())
    {
        $translator = new Translator(
            $this->getContainer($loader),
            new MessageSelector(),
            array('loader' => array('loader')),
            $options
        );

        $translator->addResource('loader', 'foo', 'fr');
        $translator->addResource('loader', 'foo', 'en');
        $translator->addResource('loader', 'foo', 'es');
        $translator->addResource('loader', 'foo', 'pt-PT'); // European Portuguese
        $translator->addResource('loader', 'foo', 'pt_BR'); // Brazilian Portuguese

        return $translator;
    }

    /**
     * Extend the test coverage of the fall back translation method.
     *
     */
    public function testFallBackTranslation()
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $translator->transChoice('choice', 'NotNumber');

    }

    /**
     * Extend the test coverage of the doTranslate method.
     */
    public function testExtendCoverageOfDoTranslate()
    {
        $translator = $this->getTranslator($this->getLoader());
        $translator->setLocale('fr');
        $translator->setFallbackLocale(array('en', 'es', 'pt-PT', 'pt_BR'));

        $translator->transChoice(null, null);
    }
}
