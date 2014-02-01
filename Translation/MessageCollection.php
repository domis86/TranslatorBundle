<?php
namespace Domis86\TranslatorBundle\Translation;

use Domis86\TranslatorBundle\Entity\Message;

/**
 * MessageCollection
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class MessageCollection
{
    /**
     * @var array
     */
    private $messagesIndexedByName = array();

    /**
     * @var bool
     */
    private $modified = false;

    /**
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $messageAsArray = $message->exportAsArray();
        $domainName = $messageAsArray['domain']['name'];
        $messageName = $messageAsArray['name'];
        $this->messagesIndexedByName[$domainName][$messageName] = $messageAsArray;
        $this->setModified(true);
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @return bool
     */
    public function hasMessage($messageName, $domainName)
    {
        if (!isset($this->messagesIndexedByName[$domainName])) {
            return false;
        }
        if (!isset($this->messagesIndexedByName[$domainName][$messageName])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $messageName
     * @param string $domainName
     * @param string $locale
     * @return string|bool Translation of Message or false(bool)
     */
    public function getTranslationOfMessageAsString($messageName, $domainName, $locale)
    {
        if (!$this->hasMessage($messageName, $domainName)) {
            return false;
        }
        $messageAsArray = $this->messagesIndexedByName[$domainName][$messageName];
        if (!isset($messageAsArray['translations'][$locale])) {
            return false;
        }
        return $messageAsArray['translations'][$locale];
    }

    /**
     * @return bool
     */
    public function isModified()
    {
        return $this->modified;
    }

    /**
     * @param bool $modified
     */
    public function setModified($modified)
    {
        $this->modified = $modified;
    }

    /**
     * @return array
     */
    public function export()
    {
        return $this->messagesIndexedByName;
    }

    /**
     * @param array $messagesIndexedByName
     */
    public function import($messagesIndexedByName)
    {
        $this->messagesIndexedByName = $messagesIndexedByName;
    }
}
