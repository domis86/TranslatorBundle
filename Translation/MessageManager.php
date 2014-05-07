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
    private $missingMessagesFromCollectionList = array(); // domain_name => message_name
    private $missingMessagesFromStorageList = array();    // domain_name => message_name
    private $usedMessagesList = array();                  // domain_name => message_name

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

    /** @var NamingVerifier */
    private $namingVerifier;

    /**
     * @param Storage $storage
     * @param CacheManager $cacheManager
     * @param NamingVerifier $namingVerifier
     */
    public function __construct(Storage $storage, CacheManager $cacheManager, NamingVerifier $namingVerifier)
    {
        $this->storage = $storage;
        $this->cacheManager = $cacheManager;
        $this->namingVerifier = $namingVerifier;

        // This will be lazy loaded at first attempt of message translation
        $this->messageCollection = null;

        // Location is unknown when this constructor is called.
        // It will be set by \Domis86\TranslatorBundle\EventListener\TranslatorControllerListener
        $this->locationOfMessages = new LocationVO(null, null, null);
    }

    /**
     * @param string $messageName
     * @param string $domainNameCandidate
     * @param string $localeCandidate
     * @return string|bool Translation(string) or false(bool)
     */
    public function translateMessage($messageName, $domainNameCandidate, $localeCandidate)
    {
        $locale = $this->namingVerifier->determineLocale($localeCandidate);
        if (!$locale) {
            return false;
        }
        $domainName = $this->namingVerifier->determineDomainName($domainNameCandidate);

        $translation = $this->tryToGetTranslationFromCollection($messageName, $domainName, $locale);
        if (!$translation) {
            // no translation has been found
            return false;
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
     * @return LocationVO
     */
    public static function getLocationOfBackendAction()
    {
        return new LocationVO('Domis86\\TranslatorBundle', 'Controller\\TranslatorController', 'backendAction');
    }

    /**
     * Add missing objects to Storage and update cache if necessary
     */
    public function handleMissingObjects()
    {
        if (!empty($this->missingMessagesFromCollectionList)) {
            $this->storage->addMissingDomains(array_keys($this->missingMessagesFromStorageList));
            $this->storage->addMissingMessages($this->missingMessagesFromStorageList);
            $this->storage->addMissingMessageLocations($this->missingMessagesFromCollectionList, $this->locationOfMessages);
            $this->missingMessagesFromCollectionList = array();
            $this->missingMessagesFromStorageList = array();

            $this->messageCollection = $this->storage->loadMessageCollectionForLocation($this->locationOfMessages);
        }
        if ($this->messageCollection) {
            $this->cacheManager->saveMessageCollectionForLocation($this->locationOfMessages, $this->messageCollection);
        }
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @param string $locale
     * @return string|bool Translation(string) or false(bool)
     */
    private function tryToGetTranslationFromCollection($messageName, $domainName, $locale)
    {
        // abort if Message doesn't exists in Storage
        if (isset($this->missingMessagesFromStorageList[$domainName][$messageName])) return false;

        $this->markMessageAsUsed($messageName, $domainName);

        if (!$this->messageCollection) {
            $this->messageCollection = $this->cacheManager->loadMessageCollectionForLocation($this->locationOfMessages);
            if (!$this->messageCollection) {
                $this->messageCollection = $this->storage->loadMessageCollectionForLocation($this->locationOfMessages);
            }
        }

        if (!$this->messageCollection->hasMessage($messageName, $domainName)) {
            try {
                $this->namingVerifier->verifyNames($messageName, $domainName);
                $this->markMessageAsMissingFromCollection($messageName, $domainName);
                $message = $this->storage->getMessage($messageName, $domainName);
                if (!$message) {
                    $this->markMessageAsMissingFromStorage($messageName, $domainName);
                    return false;
                }
                $this->messageCollection->addMessage($message);
            }
            catch (\InvalidArgumentException $e) {
                return false;
            }
        }

        return $this->messageCollection->getTranslationOfMessageAsString($messageName, $domainName, $locale);
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
        $this->missingMessagesFromCollectionList[$domainName][$messageName] = true;
    }

    /**
     * Mark that this Message does'nt exists in Storage (db)
     * @param string $messageName
     * @param string $domainName
     */
    private function markMessageAsMissingFromStorage($messageName, $domainName)
    {
        $this->missingMessagesFromStorageList[$domainName][$messageName] = true;
    }
}
