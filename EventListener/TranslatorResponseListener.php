<?php

namespace Domis86\TranslatorBundle\EventListener;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Templating\Asset\PackageInterface;
use Domis86\TranslatorBundle\Translation\MessageManager;

/**
 * TranslatorResponseListener
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class TranslatorResponseListener
{
    /**
     * @var bool
     */
    private $isWebDebugDialogEnabled = false;

    /**
     * @var MessageManager
     */
    private $messageManager;

    /**
     * @var PackageInterface
     */
    private $templatingHelperAssets;

    public function __construct(MessageManager $messageManager, PackageInterface $templatingHelperAssets)
    {
        $this->messageManager = $messageManager;
        $this->templatingHelperAssets = $templatingHelperAssets;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        $this->messageManager->handleMissingObjects();
        if (!$this->isWebDebugDialogEnabled) {
            return;
        }
// TODO: decide if this should try inject Domis86WebDebugDialog only in MASTER_REQUEST
//        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
//            return;
//        }
        $this->injectJavascriptToResponse($event->getResponse());
    }

    public function enableWebDebugDialog()
    {
        $this->isWebDebugDialogEnabled = true;
    }

    /**
     * Injects loadWebDebugDialog.js into Response.
     *
     * @param Response $response A Response instance
     */
    private function injectJavascriptToResponse(Response $response)
    {
        if ($response->headers->has('Domis86TranslatorDialog-Token')) {
            return;
        }

        if (function_exists('mb_stripos')) {
            $posrFunction = 'mb_strripos';
            $substrFunction = 'mb_substr';
        } else {
            $posrFunction = 'strripos';
            $substrFunction = 'substr';
        }
        $content = $response->getContent();
        $pos = $posrFunction($content, '</body>');
        if (false === $pos) {
            return;
        }

        // do inject
        $jsUrl = $this->templatingHelperAssets->getUrl('bundles/domis86translator/js/loadWebDebugDialog.js');
        $jsScript = '<script type="text/javascript" src=' . $jsUrl . '></script>';
        $content = $substrFunction($content, 0, $pos) . $jsScript . $substrFunction($content, $pos);
        $response->setContent($content);
        $response->headers->set('Domis86TranslatorDialog-Token', 1);
    }
}
