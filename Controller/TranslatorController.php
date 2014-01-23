<?php

namespace Domis86\TranslatorBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Domis86\TranslatorBundle\Translation\LocationVO;
use Domis86\TranslatorBundle\Translation\MessageManager;
use Domis86\TranslatorBundle\Translation\WebDebugDialog;


class TranslatorController
{
    /** @var EngineInterface */
    private $templating = null;

    /** @var MessageManager */
    private $messageManager = null;

    /** @var WebDebugDialog */
    private $webDebugDialog = null;

    /** @var TranslatorInterface */
    private $translator = null;

    function __construct(EngineInterface $templating, MessageManager $messageManager, WebDebugDialog $webDebugDialog, TranslatorInterface $translator)
    {
        $this->templating = $templating;
        $this->messageManager = $messageManager;
        $this->webDebugDialog = $webDebugDialog;
        $this->translator = $translator;
    }

    public function saveMessageAction(Request $request)
    {
        // TODO: add security

        $messageTranslation = $this->messageManager->saveMessageTranslationByMessageId(
            $request->request->get('message_id'),
            $request->request->get('message_translation_locale'),
            $request->request->get('value')
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
            'Domis86TranslatorBundle:DataCollector:webDebugDialog.html.twig',
            array(
                'webDebugDialog' => $this->webDebugDialog->getData($location)
            , 'location' => $location
            )
        );
    }

    public function exampleAction()
    {
        $exampleTranslations = array();
        $exampleTranslations['hello']['fr'] = $this->translator->trans('hello', array(), 'messages', 'fr');
        $exampleTranslations['hello']['en'] = $this->translator->trans('hello', array(), 'messages', 'en');
        $exampleTranslations['beer']['fr'] = $this->translator->trans('beer', array(), 'messages', 'fr');
        $exampleTranslations['beer']['en'] = $this->translator->trans('beer', array(), 'messages', 'en');
        $exampleTranslations['some info']['fr'] = $this->translator->trans('some info', array(), 'infos', 'fr');
        $exampleTranslations['some info']['en'] = $this->translator->trans('some info', array(), 'infos', 'en');

        return $this->templating->renderResponse(
            'Domis86TranslatorBundle:Translator:example.html.twig',
            array('exampleTranslations' => $exampleTranslations)
        );
    }
}
