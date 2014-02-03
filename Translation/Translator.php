<?php

namespace Domis86\TranslatorBundle\Translation;

use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Translator
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class Translator implements TranslatorInterface
{
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

    public function __construct(MessageManager $messageManager, MessageSelector $selector = null)
    {
        $this->messageManager = $messageManager;
        $this->selector = $selector ? : new MessageSelector();
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (!$locale) {
            $locale = $this->getLocale();
        }
        if ($translation = $this->messageManager->translateMessage($id, $domain, $locale)) {
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
        if ($translation = $this->messageManager->translateMessage($id, $domain, $locale)) {
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
    public function setParentTranslator(TranslatorInterface $parentTranslator) {
        $this->parentTranslator = $parentTranslator;
    }
}
