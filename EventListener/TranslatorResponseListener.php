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

        // TODO: add list of ignored locations?
        $location = $this->messageManager->getLocationOfMessages();
        if ($location->isEqualTo($this->messageManager->getLocationOfBackendAction())) {
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

        // prepare inject
        // TODO: Get assets from config. Create template and render here.
        $assets = array(
            'asset_jquery_url'         => '/bundles/domis86translator/js/external/jquery-2.0.3.min.js',
            'asset_jquery_ui_url'      => '/bundles/domis86translator/js/external/jquery-ui.1.10.3.min.js',
            'asset_jquery_ui_css_url'  => '/bundles/domis86translator/css/jquery-ui.1.10.3.css',
            'asset_datatables_url'     => '/bundles/domis86translator/js/external/jquery.dataTables.1.10.0-dev.min.js',
            'asset_datatables_css_url' => '/bundles/domis86translator/css/jquery.dataTables.css',
            'asset_jeditable_url'      => '/bundles/domis86translator/js/external/jquery.jeditable.mini.js',
            'asset_webdebugdialog_url' => '/bundles/domis86translator/js/webDebugDialog.js'
        );

        $html = '<span id="domis86_data_for_loadwebdebugdialogjs" style="display:none;"';
        foreach ($assets as $name=>$asset) {
            $assetUrl = $this->templatingHelperAssets->getUrl($asset);
            $html .= ' data-' . $name . '="' . $assetUrl . '"';
        }
        $html .= '></span>';
        $jsUrl = $this->templatingHelperAssets->getUrl('bundles/domis86translator/js/loadWebDebugDialog.js');
        $html .= '<script type="text/javascript" src="' . $jsUrl . '"></script>';

        // do inject
        $content = $substrFunction($content, 0, $pos) . $html . $substrFunction($content, $pos);
        $response->setContent($content);
        $response->headers->set('Domis86TranslatorDialog-Token', 1);
    }
}
