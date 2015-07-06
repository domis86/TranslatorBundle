<?php

namespace Domis86\TranslatorBundle\EventListener;

use Domis86\TranslatorBundle\Translation\Translator;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Domis86\TranslatorBundle\Translation\MessageManager;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * TranslatorResponseListener
 *
 * @author Dominik Frankowicz <domis86@gmail.com>
 */
class TranslatorResponseListener
{
    /** @var bool */
    private $isWebDebugDialogEnabled = false;

    /** @var MessageManager */
    private $messageManager;

    /** @var EngineInterface */
    private $templating = null;

    /** @var array */
    private $bundleConfig = array();

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(MessageManager $messageManager, EngineInterface $templating, array $bundleConfig, TranslatorInterface $translator)
    {
        $this->messageManager = $messageManager;
        $this->templating = $templating;
        $this->bundleConfig = $bundleConfig;
        $this->translator = $translator;
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if ((!$this->translator instanceof Translator)
            || !$this->translator->isEnabled()
            || HttpKernel::MASTER_REQUEST != $event->getRequestType()
        ) {
            return;
        }

        $this->messageManager->handleMissingObjects();

        if (!$this->isWebDebugDialogEnabled) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($response->isRedirection()
            || ($response->headers->has('Content-Type') && false === strpos($response->headers->get('Content-Type'), 'html'))
            || 'html' !== $request->getRequestFormat()
        ) {
            // not html or is redirect --> do not inject
            return;
        }

        $location = $this->messageManager->getLocationOfMessages();
        if ($location->isEqualTo(MessageManager::getLocationOfBackendAction())) {
            return;
        }

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

        $assets = $this->templating->render(
            'Domis86TranslatorBundle:Translator:assets.html.twig',
            array(
                'bundleConfig' => $this->bundleConfig,
                'backendMode' => false
            )
        );
        $assets = "\n".str_replace("\n", '', $assets);

        // do inject
        $content = $substrFunction($content, 0, $pos) . $assets . $substrFunction($content, $pos);
        $response->setContent($content);
        $response->headers->set('Domis86TranslatorDialog-Token', 1);
    }
}
