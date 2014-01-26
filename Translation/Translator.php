<?php

namespace Domis86\TranslatorBundle\Translation;

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

    public function __construct(TranslatorInterface $parentTranslator, MessageManager $messageManager)
    {
        $this->parentTranslator = $parentTranslator;
        $this->messageManager = $messageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        if (!$locale) {
            $locale = $this->getLocale();
        }
        if ($translation = $this->messageManager->translateMessage($id, $domain, $locale, $parameters)) {
            return $translation;
        }
        return $this->parentTranslator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        // TODO: implement, with MessageSelector?
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
}
