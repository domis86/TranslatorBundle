<?php

namespace Domis86\TranslatorBundle\EventListener;

use Domis86\TranslatorBundle\Translation\Translator;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TranslatorConsoleListener
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class TranslatorConsoleListener
{
    const LOCATION_NOT_FOUND = 'not found command';

    /** @var MessageManager */
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

    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        if (!($this->translator instanceof Translator)) {
            return;
        }

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

        if (TranslatorControllerListener::isIgnored($commandClassName, $actionName, $this->whitelistedControllersRegexes, $this->ignoredControllersRegexes)) {
            return;
        }

        $locationOfMessages = new LocationVO($bundleName, $controllerName, $actionName);
        $this->messageManager->setLocationOfMessages($locationOfMessages);
        $this->translator->enable();
    }

    public function onConsoleTerminate()
    {
        if (!($this->translator instanceof Translator) || !$this->translator->isEnabled()) {
            return;
        }
        $this->messageManager->handleMissingObjects();
    }

}
