<?php

namespace Domis86\TranslatorBundle\EventListener;

use Domis86\TranslatorBundle\Translation\Translator;
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

    /** @var Translator */
    private $translator;

    /** @var array */
    private $ignoredControllersRegexes;

    public function __construct(MessageManager $messageManager, Translator $translator, $ignoredControllersRegexes)
    {
        $this->messageManager = $messageManager;
        $this->translator = $translator;
        $this->ignoredControllersRegexes = $ignoredControllersRegexes;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $controllerClassName = '';
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

        if ($this->isIgnored($controllerClassName . '::' . $actionName)) {
            return;
        }

        $locationOfMessages = new LocationVO($bundleName, $controllerName, $actionName);
        $this->messageManager->setLocationOfMessages($locationOfMessages);
        $this->translator->enable();
    }

    private function isIgnored($className)
    {
        foreach ($this->ignoredControllersRegexes as $regex) {
            if (preg_match($regex, $className, $matches)) {
                return true;
            }
        }
        return false;
    }
}
