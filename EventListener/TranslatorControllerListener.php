<?php

namespace Domis86\TranslatorBundle\EventListener;

use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageManager;

/**
 * TranslatorControllerListener
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class TranslatorControllerListener
{
    const LOCATION_NOT_FOUND = 'not found';
    /**
     * @var MessageManager
     */
    private $messageManager;

    public function __construct(MessageManager $messageManager)
    {
        $this->messageManager = $messageManager;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $bundleName = self::LOCATION_NOT_FOUND;
        $controllerName = self::LOCATION_NOT_FOUND;
        $actionName = self::LOCATION_NOT_FOUND;

        $controller = $event->getController();

        // $controller passed can be either a class(in array format) or a Closure
        // (see Symfony\Bundle\FrameworkBundle\Controller\ControllerResolver or ControllerNameParser for more info)
        if (is_array($controller)) {
            $actionName = $controller[1];
            $controllerClassName = get_class($controller[0]);

            preg_match('/(.+[^\\\\]Bundle)\\\\(.+)/', $controllerClassName, $matches);
            if ($matches) {
                $bundleName = $matches[1];
                $controllerName = $matches[2];
            }
        }
        $locationOfMessages = new LocationVO($bundleName, $controllerName, $actionName);
        $this->messageManager->setLocationOfMessages($locationOfMessages);
    }
}
