<?php

namespace Domis86\TranslatorBundle\Storage;

use Doctrine\ORM\EntityManager;
use Domis86\TranslatorBundle\Entity\Domain;
use Domis86\TranslatorBundle\Entity\DomainRepository;
use Domis86\TranslatorBundle\Entity\Message;
use Domis86\TranslatorBundle\Entity\MessageRepository;
use Domis86\TranslatorBundle\Entity\MessageLocation;
use Domis86\TranslatorBundle\Entity\MessageLocationRepository;
use Domis86\TranslatorBundle\Entity\MessageTranslation;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageCollection;

class Storage implements StorageInterface
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

// TODO: is this method needed?
//    /**
//     * @param string $messageName
//     * @param string $domainName
//     * @return bool
//     */
//    public function hasMessage($messageName, $domainName)
//    {
//        $messageRepository = $this->getMessageRepository();
//        $message = $messageRepository->findOneByNameAndDomain($messageName, $domainName);
//        if (!$message) {
//            return false;
//        }
//        return true;
//    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @return Message|bool
     */
    public function getMessage($messageName, $domainName)
    {
        $messageRepository = $this->getMessageRepository();
        return $messageRepository->findOneByNameAndDomain($messageName, $domainName);
    }

    /**
     * @param string LocationVO $location
     * @return array
     */
    public function loadMessagesForLocation(LocationVO $location)
    {
        $messageRepository = $this->getMessageRepository();
        return $messageRepository->loadByLocation($location);
    }

    /**
     * @param string LocationVO $location
     * @return MessageCollection
     */
    public function loadMessageCollectionForLocation(LocationVO $location)
    {
        $messageCollection = new MessageCollection();
        $messages = $this->loadMessagesForLocation($location);
        foreach ($messages as $message) {
            $messageCollection->addMessage($message);
        }
        return $messageCollection;
    }

    /**
     * @param $messageId
     * @param string $locale
     * @param string $translation
     * @return MessageTranslation|bool
     */
    public function saveMessageTranslation($messageId, $locale, $translation)
    {
        $messageRepository = $this->getMessageRepository();
        /** @var Message $message */
        $message = $messageRepository->find($messageId);
        if (!$message) {
            return false;
        }
        $messageTranslation = $message->getTranslationForLocale($locale);
        if (!$messageTranslation) {
            $messageTranslation = new MessageTranslation();
            $messageTranslation->setMessage($message);
            $messageTranslation->setMessageId($message->getId());
            $messageTranslation->setLocale($locale);
            $this->entityManager->persist($messageTranslation);
            $message->addTranslation($messageTranslation);
        }
        $messageTranslation->setTranslation($translation);
        $this->entityManager->flush();
        return $messageTranslation;
    }

    /**
     * @param array $missingMessagesList
     */
    public function addMissingMessages($missingMessagesList = array())
    {
        if (empty($missingMessagesList)) return;
        $messageRepository = $this->getMessageRepository();
        $domains = $this->getAllDomainsAsArray();
        // persist missing Messages from the list
        foreach ($missingMessagesList as $domainName => $missingMessagesOfDomain) {
            foreach ($missingMessagesOfDomain as $missingMessageName => $isMissing) {
                if ($messageRepository->findOneByNameAndDomain($missingMessageName, $domainName)) {
                    // Message already exists --> skip
                    continue;
                }
                $message = new Message();
                $message->setName($missingMessageName);
                $message->setDomain($domains[$domainName]);
                $this->entityManager->persist($message);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param array $missingDomainNames Array of names of Domains
     */
    public function addMissingDomains($missingDomainNames = array())
    {
        if (empty($missingDomainNames)) return;

        $domains = $this->getAllDomainsAsArray();
        foreach ($missingDomainNames as $missingDomainName) {
            if (!isset($domains[$missingDomainName])) {
                $domain = new Domain();
                $domain->setName($missingDomainName);
                $this->entityManager->persist($domain);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param array $missingMessagesList
     * @param LocationVO $locationOfMessages
     */
    public function addMissingMessageLocations($missingMessagesList, LocationVO $locationOfMessages)
    {
        if (empty($missingMessagesList)) return;
        $messageRepository = $this->getMessageRepository();
        $messageLocationRepository = $this->getMessageLocationRepository();
        foreach ($missingMessagesList as $domainName => $missingMessagesOfDomain) {
            foreach ($missingMessagesOfDomain as $missingMessageName => $isMissing) {
                $message = $messageRepository->findOneByNameAndDomain($missingMessageName, $domainName);
                if ($messageLocationRepository->findOneByMessageAndLocationVO($message, $locationOfMessages)) {
                    // MessageLocation already exists --> skip
                    continue;
                }
                $messageLocation = new MessageLocation();
                $messageLocation->setMessage($message);
                $messageLocation->setLocation($locationOfMessages);
                $this->entityManager->persist($messageLocation);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @return array
     */
    private function getAllDomainsAsArray()
    {
        return $this->getDomainRepository()->findAllAsArray();
    }

    /**
     * @return MessageRepository
     */
    private function getMessageRepository()
    {
        return $this->entityManager->getRepository("Domis86TranslatorBundle:Message");
    }

    /**
     * @return DomainRepository
     */
    private function getDomainRepository()
    {
        return $this->entityManager->getRepository("Domis86TranslatorBundle:Domain");
    }

    /**
     * @return MessageLocationRepository
     */
    private function getMessageLocationRepository()
    {
        return $this->entityManager->getRepository("Domis86TranslatorBundle:MessageLocation");
    }
}