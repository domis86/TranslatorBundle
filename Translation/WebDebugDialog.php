<?php

namespace Domis86\TranslatorBundle\Translation;

use Domis86\TranslatorBundle\Entity\Message;
use Domis86\TranslatorBundle\Entity\MessageLocation;
use Domis86\TranslatorBundle\Storage\Storage;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * WebDebugDialog
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class WebDebugDialog
{
    /** @var TranslatorInterface */
    private $parentTranslator;

    /** @var Storage */
    private $storage;

    /** @var CacheManager */
    private $cacheManager;

    /** @var NamingVerifier */
    private $namingVerifier;

    /** @var array */
    private $managedLocales;

    public function __construct(Storage $storage, CacheManager $cacheManager, NamingVerifier $namingVerifier, array $managedLocales)
    {
        $this->storage = $storage;
        $this->cacheManager = $cacheManager;
        $this->namingVerifier = $namingVerifier;
        $this->managedLocales = $managedLocales;
    }

    public function clearCache()
    {
        return $this->cacheManager->clearCache();
    }

    /**
     * @param LocationVO $location
     * @return array
     */
    public function getDataForLocation(LocationVO $location)
    {
        $messages = $this->storage->loadMessagesForLocation($location);
        return $this->parseDataForView($messages);
    }

    /**
     * @return array
     */
    public function getDataForBackend()
    {
        $messages = $this->storage->loadAllMessages();
        return $this->parseDataForView($messages, true);
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @param string $locale
     * @param string $uncleanTranslation
     * @return string|bool
     */
    public function saveMessageTranslation($messageName, $domainName, $locale, $uncleanTranslation)
    {
        if (!$this->namingVerifier->verifyLocale($locale)) {
            return false;
        }
        $this->namingVerifier->verifyNames($messageName, $domainName);

        $message = $this->storage->getMessage($messageName, $domainName);
        if (!$message) {
            return false;
        }

        $translation = $this->namingVerifier->determineTranslation($uncleanTranslation);
        if ($translation === false) {
            $this->storage->removeMessageTranslation($message, $locale);
            return '';
        }

        $messageTranslation = $this->storage->saveMessageTranslation($message, $locale, $translation);

        // update cache for Locations of this Message
        $locations = $messageTranslation->getMessage()->getArrayOfLocationVOs();
        foreach ($locations as $location) {
            $messageCollection = $this->storage->loadMessageCollectionForLocation($location);
            $this->cacheManager->saveMessageCollectionForLocation($location, $messageCollection);
        }
        return $messageTranslation->getTranslation();
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @return string|bool
     */
    public function deleteMessage($messageName, $domainName)
    {
        $this->storage->deleteMessage($messageName, $domainName);
        $this->cacheManager->clearCache();
        return true;
    }

    /**
     * @param array $messages
     * @param bool $backendMode
     * @return array
     */
    private function parseDataForView($messages, $backendMode = false)
    {
        $defaultLocale = $this->managedLocales[0];

        $messagesForView = array();
        foreach ($messages as $message) {
            /** @var Message $message */

            $x = array();
            $x['id'] = $message->getId();
            $x['name'] = $message->getName();
            $x['domain_name'] = $message->getDomain()->getName();

            $translations = array();
            foreach ($this->managedLocales as $locale) {
                $translations[$locale] = '';
                if ($messageTranslation = $message->getTranslationForLocale($locale)) {
                    $translations[$locale] = $messageTranslation->getTranslation();
                }
            }
            $x['translations'] = $translations;

            $parentTranslations = array();
            foreach ($this->managedLocales as $locale) {
                $parentTranslations[$locale] = '';
                $parentTranslation = $this->parentTranslator->trans($x['name'], array(), $x['domain_name'], $locale);
                if (!$parentTranslation) {
                    continue;
                }
                if ($defaultLocale == $locale || $parentTranslation != $x['name']) {
                    $parentTranslations[$locale] = $parentTranslation;
                }
            }
            $x['parentTranslations'] = $parentTranslations;

            if ($backendMode) {
                $locations = array();
                foreach ($message->getMessageLocations() as $aLocation) {
                    /** @var MessageLocation $aLocation */
                    $locations[] = "{$aLocation->getBundle()}\\{$aLocation->getController()}:{$aLocation->getAction()}";
                }
                $x['locations'] = $locations;
            }

            $messagesForView[$x['id']] = $x;
        }

        $dataResult = array();
        $dataResult['managedLocales'] = $this->managedLocales;
        $dataResult['messagesForView'] = $messagesForView;
        return $dataResult;
    }

    /**
     * @param TranslatorInterface $parentTranslator
     */
    public function setParentTranslator(TranslatorInterface $parentTranslator) {
        $this->parentTranslator = $parentTranslator;
    }
}
