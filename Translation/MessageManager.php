<?php
namespace Domis86\TranslatorBundle\Translation;

use Domis86\TranslatorBundle\Storage\Storage;

/**
 * MessageManager
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class MessageManager
{
    // TODO: refactor these? (convert to objects?):
    private $missingMessagesList = array(); // domain_name => message_name
    private $usedMessagesList = array(); // domain_name => message_name

    /**
     * @var LocationVO $locationOfMessages Location of Messages in current request
     */
    private $locationOfMessages;

    /** @var MessageCollection */
    private $messageCollection;

    /** @var Storage */
    private $storage;

    /** @var CacheManager */
    private $cacheManager;

    /** @var array */
    private $managedLocales;

    /**
     * @param Storage $storage
     * @param CacheManager $cacheManager
     * @param array $managedLocales
     */
    public function __construct(Storage $storage, CacheManager $cacheManager, array $managedLocales)
    {
        $this->storage = $storage;
        $this->cacheManager = $cacheManager;
        $this->managedLocales = $managedLocales;

        // This will be lazy loaded at first attempt of message translation
        $this->messageCollection = null;

        // Location is unknown when this constructor is called.
        // It will be set by \Domis86\TranslatorBundle\EventListener\TranslatorControllerListener
        $this->locationOfMessages = new LocationVO(null, null, null);
    }

    /**
     * @param string $uncleanMessageName
     * @param string $uncleanDomainName
     * @param string $uncleanLocale
     * @param array $parameters
     * @return string|bool Translation(string) or false(bool)
     */
    public function translateMessage($uncleanMessageName, $uncleanDomainName, $uncleanLocale, array $parameters = array())
    {
        $locale = $this->determineLocale($uncleanLocale);
        if ($locale === false) {
            // invalid locale
            return false;
        }
        $messageName = $this->determineMessageName($uncleanMessageName);
        if ($messageName === false) {
            // invalid (empty?) message name
            return false;
        }
        $domainName = $this->determineDomainName($uncleanDomainName);

        $translation = $this->tryToGetTranslationFromCollection($messageName, $domainName, $locale);
        if (!$translation) {
            // no translation has been found
            return false;
        }
        if (!empty($parameters)) {
            // TODO: check handling of parameters
            return strtr($translation, $parameters);
        }
        return $translation;
    }

    /**
     * Describe location of Messages from current request. Called by listener.
     * @param LocationVO $locationVO
     */
    public function setLocationOfMessages(LocationVO $locationVO)
    {
        $this->locationOfMessages = $locationVO;
    }

    /**
     * @return LocationVO
     */
    public function getLocationOfMessages()
    {
        return $this->locationOfMessages;
    }

    /**
     * Add missing objects to Storage and update cache if necessary
     */
    public function handleMissingObjects()
    {
        if (!empty($this->missingMessagesList)) {
            $this->storage->addMissingDomains(array_keys($this->missingMessagesList));
            $this->storage->addMissingMessages($this->missingMessagesList);
            $this->storage->addMissingMessageLocations($this->missingMessagesList, $this->locationOfMessages);
            $this->missingMessagesList = array();

            $this->messageCollection = $this->storage->loadMessageCollectionForLocation($this->locationOfMessages);
        }
        if ($this->messageCollection) {
            $this->cacheManager->saveMessageCollectionForLocation($this->locationOfMessages, $this->messageCollection);
        }
    }

    /**
     * @param $messageId
     * @param string $uncleanLocale
     * @param string $translation
     * @return string|bool
     */
    public function saveMessageTranslationByMessageId($messageId, $uncleanLocale, $translation)
    {
        $locale = $this->determineLocale($uncleanLocale);
        if ($locale === false) {
            // invalid locale
            return false;
        }
        $messageTranslation = $this->storage->saveMessageTranslation($messageId, $locale, $translation);
        if (!$messageTranslation) {
            return false;
        }

        // update cache for Locations of this Message
        $locations = $messageTranslation->getMessage()->getArrayOfLocationVOs();
        foreach ($locations as $location) {
            $messageCollection = $this->storage->loadMessageCollectionForLocation($location);
            $this->cacheManager->saveMessageCollectionForLocation($location, $messageCollection);
        }
        return $messageTranslation->getTranslation();
    }

    /**
     * @return array
     */
    public function getManagedLocales()
    {
        return $this->managedLocales;
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @param string $locale
     * @return string|bool Translation(string) or false(bool)
     */
    private function tryToGetTranslationFromCollection($messageName, $domainName, $locale)
    {
        $this->markMessageAsUsed($messageName, $domainName);

        if (!$this->messageCollection) {
            $this->messageCollection = $this->cacheManager->loadMessageCollectionForLocation($this->locationOfMessages);
            if (!$this->messageCollection) {
                $this->messageCollection = $this->storage->loadMessageCollectionForLocation($this->locationOfMessages);
            }
        }

        if (!$this->messageCollection->hasMessage($messageName, $domainName)) {
            $this->markMessageAsMissingFromCollection($messageName, $domainName);
            $message = $this->storage->getMessage($messageName, $domainName);
            if (!$message) {
                return false;
            }
            $this->messageCollection->addMessage($message);
        }

        return $this->messageCollection->getTranslationOfMessageAsString($messageName, $domainName, $locale);
    }

    /**
     * @param string $uncleanLocale
     * @return string|bool Locale(string) or false(bool) if given locale is not in %domis86_translator.managed_locales%
     */
    private function determineLocale($uncleanLocale)
    {
        $locale = trim($uncleanLocale);
        if (strlen($locale)<1) {
            // return default locale
            return $this->managedLocales[0];
        }
        if (!in_array($locale, $this->managedLocales)) {
            // we don't manage this locale
            return false;
        }
        return $locale;
    }

    /**
     * @param string $uncleanDomainName
     * @return string Clean Domain name
     */
    private function determineDomainName($uncleanDomainName)
    {
        $domainName = trim($uncleanDomainName);
        if (!$domainName || strlen($domainName) < 1) {
            return 'messages';
        }
        return $domainName;
    }

    /**
     * @param string $uncleanMessageName
     * @return string|bool Clean Message name or false(bool)
     */
    private function determineMessageName($uncleanMessageName)
    {
        $messageName = trim($uncleanMessageName);
        if (!$messageName || strlen($messageName) < 1) {
            // this Message name is not valid
            return false;
        }
        return $messageName;
    }

    /**
     * Mark that this Message has been used in current Location
     * @param string $messageName
     * @param string $domainName
     */
    private function markMessageAsUsed($messageName, $domainName)
    {
        $this->usedMessagesList[$domainName][$messageName] = true;
    }

    /**
     * Mark that this Message is not loaded for current Location
     * @param string $messageName
     * @param string $domainName
     */
    private function markMessageAsMissingFromCollection($messageName, $domainName)
    {
        $this->missingMessagesList[$domainName][$messageName] = true;
    }
}
