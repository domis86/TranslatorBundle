<?php

namespace Domis86\TranslatorBundle\Translation;

use Domis86\TranslatorBundle\Entity\Message;
use Domis86\TranslatorBundle\Storage\Storage;
use Symfony\Component\Translation\TranslatorInterface;


/**
 * WebDebugDialog
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class WebDebugDialog
{
    /** @var Storage */
    private $storage;

    /** @var MessageManager */
    private $messageManager;

    /** @var TranslatorInterface */
    private $parentTranslator;

    public function __construct(Storage $storage, MessageManager $messageManager, TranslatorInterface $parentTranslator)
    {
        $this->storage = $storage;
        $this->messageManager = $messageManager;
        $this->parentTranslator = $parentTranslator;
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
        return $this->parseDataForView($messages);
    }

    /**
     * @param $messages
     * @return array
     */
    private function parseDataForView($messages)
    {
        $managedLocales = $this->messageManager->getManagedLocales();

        $messagesForView = array(); // TODO: implement as data object?
        foreach ($messages as $message) {
            /** @var Message $message */

            $x = array();
            $x['id'] = $message->getId();
            $x['name'] = $message->getName();
            $x['domain_name'] = $message->getDomain()->getName();

            $translations = array();
            foreach ($managedLocales as $locale) {
                $translations[$locale] = '';
                if ($messageTranslation = $message->getTranslationForLocale($locale)) {
                    $translations[$locale] = $messageTranslation->getTranslation();
                }
            }
            $x['translations'] = $translations;

            $parentTranslations = array();
            foreach ($managedLocales as $locale) {
                $parentTranslations[$locale] = '';
                $parentTranslation = $this->parentTranslator->trans($x['name'], array(), $x['domain_name'], $locale);
                if ($parentTranslation != $x['name']) {
                    $parentTranslations[$locale] = $parentTranslation;
                }
            }
            $x['parentTranslations'] = $parentTranslations;

            $messagesForView[$x['id']] = $x;
        }

        // TODO: implement as data object?
        $dataResult = array();
        $dataResult['managedLocales'] = $managedLocales;
        $dataResult['messagesForView'] = $messagesForView;
        return $dataResult;
    }
}
