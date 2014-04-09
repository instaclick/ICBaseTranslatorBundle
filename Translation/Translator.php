<?php
/**
 * @copyright 2013 Instaclick Inc.
 */
namespace IC\Bundle\Base\TranslatorBundle\Translation;

use Symfony\Bundle\FrameworkBundle\Translation\Translator as FrameworkTranslator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Translator.
 *
 * @author Guilherme Blanco <gblanco@nationalfibre.net>
 * @author David Maignan <davidm@nationalfibre.net>
 * @author John Zhang <johnz@nationalfibre.net>
 */
class Translator extends FrameworkTranslator
{
    /**
     * @var \Symfony\Component\Translation\MessageSelector
     */
    protected $selector;

    /**
     * Constructor.
     *
     * Available options:
     *
     *   * cache_dir: The cache directory (or null to disable caching)
     *   * debug:     Whether to enable debugging or not (false by default)
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container A ContainerInterface instance
     * @param \Symfony\Component\Translation\MessageSelector            $selector  The message selector for pluralization
     * @param array                                                     $loaderIds An array of loader Ids
     * @param array                                                     $options   An array of options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(ContainerInterface $container, MessageSelector $selector, $loaderIds = array(), array $options = array())
    {
        $this->selector = $selector;

        parent::__construct($container, $selector, $loaderIds, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        try {
            return $this->doTranslate($id, null, $parameters, $domain, $locale);
        } catch (\RuntimeException $exception) {
            return parent::trans($id, $parameters, $domain, $locale);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @api
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        try {
            return $this->doTranslate($id, $number, $parameters, $domain, $locale);
        } catch (\RuntimeException $exception) {
            return parent::transChoice($id, $number, $parameters, $domain, $locale);
        }
    }

    /**
     * Translate a pattern with a MessageFormatter
     *
     * @param string  $id
     * @param integer $number
     * @param array   $parameters
     * @param null    $domain
     * @param null    $locale
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    private function doTranslate($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $locale = $locale ?: $this->getLocale();
        $domain = $domain ?: 'messages';

        if ( ! isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        $id = (string) $id;

        $catalogue = $this->catalogues[$locale];

        while ( ! $catalogue->defines($id, $domain)) {
            if ( ! $fallbackCatalogue = $catalogue->getFallbackCatalogue()) {
                break;
            }

            $catalogue = $fallbackCatalogue;
            $locale    = $catalogue->getLocale();
        }

        $pattern = $catalogue->get($id, $domain);

        if ( ! $this->isCompatibleWithMessageFormatter($pattern)) {
            return $this->useFallbackTranslation($pattern, $number, $parameters, $locale);
        }

        if ($number) {
            array_unshift($parameters, $number);
        }

        $messageFormatter = new \MessageFormatter($locale, $pattern);

        if ( ! $messageFormatter) {
            throw new \RuntimeException('Unable to translate the message with invalid format.');
        }

        $message = $messageFormatter->format($parameters);

        if ($messageFormatter->getErrorCode() !== U_ZERO_ERROR) {
            throw new \RuntimeException('Unable to translate the message (CODE:' . $messageFormatter->getErrorCode() . '.');
        }

        return $message;
    }

    /**
     * Fallback translation
     *
     * @param string  $pattern
     * @param integer $number
     * @param array   $parameters
     * @param string  $locale
     *
     * @return string
     */
    private function useFallbackTranslation($pattern, $number, array $parameters, $locale)
    {
        if (is_int($number) || is_float($number)) {
            return strtr($this->selector->choose($pattern, (int) $number, $locale), $parameters);
        }

        return strtr($pattern, $parameters);
    }

    /**
     * Check if the pattern is compatible with MessageFormatter.
     *
     * This is publicly accessible to test the assertion.
     *
     * This regex tests if the pattern is compatible with symfony pluralization pattern
     *
     * e.g:
     *
     * {0} zero values | ...
     * {0, 1, 2, 3} range of values | ...
     * [0,3] range of values | ...
     * ]0,1[ range of values | ...
     *
     * @param string $pattern the message pattern
     *
     * @return boolean
     */
    public function isCompatibleWithMessageFormatter($pattern)
    {
        return ! (preg_match('/^(\[|\]|\{)\d+(,\d+)*(\[|\]|\})[^\[\]\{\}\|]+\|/', $pattern) === 1);
    }
}
