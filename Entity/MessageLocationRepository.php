<?php

namespace Domis86\TranslatorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Domis86\TranslatorBundle\Translation\LocationVO;

/**
 * MessageLocationRepository
 */
class MessageLocationRepository extends EntityRepository
{
    /**
     * @param Message $message
     * @param LocationVO $locationOfMessages
     * @return MessageLocation|null
     */
    public function findOneByMessageAndLocationVO(Message $message, LocationVO $locationOfMessages)
    {
        //my_log("findOneByMessageAndLocationVO({$message->getId()}, {$locationOfMessages->getBundleName()}, {$locationOfMessages->getControllerName()}, {$locationOfMessages->getActionName()})");
        return $this->findOneBy(array(
            'message_id' => $message->getId()
        , 'bundle' => $locationOfMessages->getBundleName()
        , 'controller' => $locationOfMessages->getControllerName()
        , 'action' => $locationOfMessages->getActionName()
        ));
    }
}
