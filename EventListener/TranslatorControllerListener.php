<?php

namespace Domis86\TranslatorBundle\EventListener;

use Domis86\TranslatorBundle\Translation\Translator;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageManager;
use Symfony\Component\Translation\TranslatorInterface;

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

    /** @var TranslatorInterface */
    private $translator;

    /** @var array */
    private $whitelistedControllersRegexes;

    /** @var array */
    private $ignoredControllersRegexes;

    public function __construct(MessageManager $messageManager, TranslatorInterface $translator, $whitelistedControllersRegexes, $ignoredControllersRegexes)
    {
        $this->messageManager = $messageManager;
        $this->translator = $translator;
        $this->whitelistedControllersRegexes = $whitelistedControllersRegexes;
        $this->ignoredControllersRegexes = $ignoredControllersRegexes;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        if (!($this->translator instanceof Translator)) {
            return;
        }

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

        if (self::isIgnored($controllerClassName, $actionName, $this->whitelistedControllersRegexes, $this->ignoredControllersRegexes)) {
            return;
        }

        $locationOfMessages = new LocationVO($bundleName, $controllerName, $actionName);
        $this->messageManager->setLocationOfMessages($locationOfMessages);
        $this->translator->enable();
    }

    /**
     * @param string $className
     * @param string $actionName
     * @param array $whitelistedControllersRegexes
     * @param array $ignoredControllersRegexes
     * @return bool
     */
    public static function isIgnored($className, $actionName, $whitelistedControllersRegexes, $ignoredControllersRegexes)
    {
        $classAndAction = $className . '::' . $actionName;
        foreach ($whitelistedControllersRegexes as $regex) {
            if (preg_match($regex, $classAndAction, $matches)) {
                return false;
            }
        }
        foreach ($ignoredControllersRegexes as $regex) {
            if (preg_match($regex, $classAndAction, $matches)) {
                return true;
            }
        }
        return false;
    }
}
