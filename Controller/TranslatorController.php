<?php

namespace Domis86\TranslatorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\WebDebugDialog;


class TranslatorController
{
    /** @var EngineInterface */
    private $templating = null;

    /** @var WebDebugDialog */
    private $webDebugDialog = null;

    /** @var array */
    private $bundleConfig = array();

    function __construct(EngineInterface $templating, WebDebugDialog $webDebugDialog, array $bundleConfig)
    {
        $this->templating = $templating;
        $this->webDebugDialog = $webDebugDialog;
        $this->bundleConfig = $bundleConfig;
    }

    public function saveMessageAction(Request $request)
    {
        $messageTranslation = $this->webDebugDialog->saveMessageTranslation(
            $request->request->get('message_name'),
            $request->request->get('message_domain_name'),
            $request->request->get('message_translation_locale'),
            $request->request->get('message_translation')
        );

        $return = array();
        $return['result'] = 'failed';
        $return['value'] = '';
        if ($messageTranslation !== false) {
            $return['result'] = 'ok';
            $return['value'] = $messageTranslation;
        }
        return new Response(json_encode($return), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @param LocationVO $location
     * @return Response
     */
    public function webDebugDialogAction(LocationVO $location)
    {
        return $this->templating->renderResponse(
            'Domis86TranslatorBundle:Translator:webDebugDialog.html.twig',
            array(
                'webDebugDialog' => $this->webDebugDialog->getDataForLocation($location),
                'location' => $location,
                'backendMode' => false
            )
        );
    }

    /**
     * @return Response
     */
    public function backendAction()
    {
        if (!$this->bundleConfig['is_enabled']) {
            return new Response('Domis86TranslatorBundle is not enabled in your config.yml - more info: <a href="https://github.com/domis86/TranslatorBundle">https://github.com/domis86/TranslatorBundle</a>');
        }
        return $this->templating->renderResponse(
            'Domis86TranslatorBundle:Translator:backend.html.twig',
            array(
                'webDebugDialog' => $this->webDebugDialog->getDataForBackend(),
                'backendMode' => true,
                'bundleConfig' => $this->bundleConfig
            )
        );
    }

}
