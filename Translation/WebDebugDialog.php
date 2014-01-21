<?php

namespace Domis86\TranslatorBundle\Translation;

use Domis86\TranslatorBundle\Entity\Message;
use Domis86\TranslatorBundle\Storage\Storage;
use Domis86\TranslatorBundle\Translation\LocationVO;


/**
 * WebDebugDialog
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class WebDebugDialog
{
    /**
     * @var Storage
     */
    private $storage;

    /**
     * @var MessageManager
     */
    private $messageManager;

    public function __construct(Storage $storage, MessageManager $messageManager)
    {
        $this->storage = $storage;
        $this->messageManager = $messageManager;
    }

    public function getData(LocationVO $location)
    {
        $managedLocales = $this->messageManager->getManagedLocales();
        $messages = $this->storage->loadMessagesForLocation($location);

        $messagesForView = array(); // TODO: implement as data object?
        foreach ($messages as $message) {
            /** @var Message $message */

            $x = array();
            $x['id'] = $message->getId();
            $x['name'] = $message->getName();
            $x['domain_name'] = $message->getDomain()->getName();
            $x['is_translated'] = true;
            $translations = array();
            foreach ($managedLocales as $locale) {
                $translations[$locale] = '';
                if ($messageTranslation = $message->getTranslationForLocale($locale)) {
                    $translations[$locale] = $messageTranslation->getTranslation();
                }
            }
            $x['translations'] = $translations;

            $messagesForView[$x['id']] = $x;
        }

        // count untranslated messages
        // TODO: implement count also/only in js?
        $countUntranslatedMessages = 0;
        foreach ($messagesForView as $id => $s) {
            foreach ($s['translations'] as $translation) {
                if (strlen($translation) < 1) {
                    $countUntranslatedMessages++;
                    $messagesForView[$id]['is_translated'] = false;
                    break;
                }
            }
        }

        // TODO: implement as data object?
        $dataResult = array();
        $dataResult['managedLocales'] = $managedLocales;
        $dataResult['messagesForView'] = $messagesForView;
        $dataResult['countUsedMessages'] = count($messagesForView);
        $dataResult['countTranslatedMessages'] = $dataResult['countUsedMessages'] - $countUntranslatedMessages;
        $dataResult['countUntranslatedMessages'] = $countUntranslatedMessages;
        return $dataResult;
    }

//    private function getMessagesForView()
//    {
//        $data = $this->storage->getDataForWebDebugDialog();
//        return $data['messagesForView'];
//    }
//
//    private function getAllLocales()
//    {
//        return $this->storage->getAllLocales();
//    }
}