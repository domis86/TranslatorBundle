<?php

namespace Domis86\TranslatorBundle\Storage;

use Doctrine\ORM\EntityManager;
use Domis86\TranslatorBundle\Entity\Domain;
use Domis86\TranslatorBundle\Entity\DomainRepository;
use Domis86\TranslatorBundle\Entity\Message;
use Domis86\TranslatorBundle\Entity\MessageRepository;
use Domis86\TranslatorBundle\Entity\MessageLocation;
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
        $this->entityManager = EntityManager::create(
            $entityManager->getConnection(),
            $entityManager->getConfiguration()
        );
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @return Message|bool
     */
    public function getMessage($messageName, $domainName)
    {
        return $this->getMessageRepository()->findOneByNameAndDomain($messageName, $domainName);
    }

    /**
     * Delete Message from db by name/domain name
     *
     * @param string $messageName
     * @param string $domainName
     * @return bool
     */
    public function deleteMessage($messageName, $domainName)
    {
        $message = $this->getMessageRepository()->findOneByNameAndDomain($messageName, $domainName);
        if (!$message) {
            return false;
        }
        $this->entityManager->remove($message);
        $this->entityManager->flush();
        return true;
    }

    /**
     * Delete Messages from db
     *
     * @param Message[] $messagesForDelete
     * @return bool
     */
    public function deleteMessages($messagesForDelete)
    {
        foreach ($messagesForDelete as $message) {
            $this->entityManager->remove($message);
        }
        $this->entityManager->flush();
        return true;
    }

    /**
     * @param string LocationVO $location
     * @return array
     */
    public function loadMessagesForLocation(LocationVO $location)
    {
        return $this->getMessageRepository()->loadByLocation($location);
    }

    /**
     * @return Message[]
     */
    public function loadAllMessages()
    {
        return $this->getMessageRepository()->loadAll();
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
     * @param Message $message
     * @param string $locale
     * @param string $translation
     * @return MessageTranslation
     */
    public function saveMessageTranslation(Message $message, $locale, $translation)
    {
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
     * @param Message $message
     * @param string $locale
     */
    public function removeMessageTranslation(Message $message, $locale)
    {
        $messageTranslation = $message->getTranslationForLocale($locale);
        if (!$messageTranslation) {
            return;
        }
        $message->removeTranslation($messageTranslation);
        $this->entityManager->remove($messageTranslation);
        $this->entityManager->flush();
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
                if ($messageRepository->hasMessage($missingMessageName, $domainName)) {
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

        foreach ($missingMessagesList as $domainName => $missingMessagesOfDomain) {
            foreach ($missingMessagesOfDomain as $missingMessageName => $isMissing) {
                $messageId = $messageRepository->checkIfMessageLocationNeedsToBeAdded($missingMessageName, $domainName, $locationOfMessages);
                if (!$messageId) {
                    // MessageLocation already exists --> skip
                    continue;
                }
                $messageLocation = new MessageLocation();
                $messageLocation->setMessage($messageRepository->getMessageReference($messageId));
                $messageLocation->setLocation($locationOfMessages);
                $this->entityManager->persist($messageLocation);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->entityManager->isOpen();
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


}
