<?php

namespace Domis86\TranslatorBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageManager;

/**
 * TranslatorConsoleListener
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class TranslatorConsoleListener
{
    const LOCATION_NOT_FOUND = 'not found command';

    /**
     * @var MessageManager
     */
    private $messageManager;

    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        $command = $event->getCommand();

        $bundleName = self::LOCATION_NOT_FOUND;
        $controllerName = self::LOCATION_NOT_FOUND;
        $actionName = $command->getName();

        $commandClassName = get_class($command);
        preg_match('/(.+[^\\\\]Bundle)\\\\(.+)/', $commandClassName, $matches);
        if ($matches) {
            $bundleName = $matches[1];
            $controllerName = $matches[2];
        }

        $locationOfMessages = new LocationVO($bundleName, $controllerName, $actionName);
        $this->messageManager->setLocationOfMessages($locationOfMessages);
    }

    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();
        $this->messageManager->handleMissingObjects();
    }
}
