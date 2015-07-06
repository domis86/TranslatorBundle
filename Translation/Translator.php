<?php

namespace Domis86\TranslatorBundle\Translation;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translator
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class Translator implements TranslatorInterface, TranslatorBagInterface
{
    /** @var bool */
    private $isEnabled = false;

    /**
     * @var TranslatorInterface $translator
     */
    private $parentTranslator;

    /**
     * @var MessageManager $messageManager
     */
    private $messageManager;

    /**
     * @var MessageSelector
     */
    private $selector;

    /**
     * @var array
     */
    private $ignoredDomains;

    public function __construct(MessageManager $messageManager, MessageSelector $selector = null, $ignoredDomains = array())
    {
        $this->messageManager = $messageManager;
        $this->selector = $selector ? : new MessageSelector();
        $this->ignoredDomains = $ignoredDomains;
    }

    public function enable()
    {
        $this->isEnabled = true;
    }

    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (!$locale) {
            $locale = $this->getLocale();
        }
        if ($this->isEnabled() && !$this->isIgnoredDomain($domain) && $translation = $this->messageManager->translateMessage($id, $domain, $locale)) {
            if (empty($parameters)) {
                return $translation;
            }
            return strtr($translation, $parameters);
        }
        return $this->parentTranslator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        if (!$locale) {
            $locale = $this->getLocale();
        }
        if ($this->isEnabled() && !$this->isIgnoredDomain($domain) && $translation = $this->messageManager->translateMessage($id, $domain, $locale)) {
            $translation = $this->selector->choose($translation, (int)$number, $locale);
            if (empty($parameters)) {
                return $translation;
            }
            return strtr($translation, $parameters);
        }
        return $this->parentTranslator->transChoice($id, $number, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->parentTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->parentTranslator->getLocale();
    }

    /**
     * @param TranslatorInterface $parentTranslator
     */
    public function setParentTranslator(TranslatorInterface $parentTranslator)
    {
        $this->parentTranslator = $parentTranslator;
    }

    /**
     * @param string $domain
     * @return bool
     */
    private function isIgnoredDomain($domain)
    {
        return in_array($domain, $this->ignoredDomains);
    }

    /**
     * Gets the catalogue by locale.
     *
     * @param string|null $locale The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     *
     * @return MessageCatalogueInterface
     */
    public function getCatalogue($locale = null)
    {
        return $this->parentTranslator->getCatalogue($locale);
        if (null === $locale) {
            $locale = $this->getLocale();
        } else {
            //$this->assertValidLocale($locale); TODO: domis86-2.7
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }
}
