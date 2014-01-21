<?php

namespace Domis86\TranslatorBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Domis86\TranslatorBundle\Translation\LocationVO;


/**
 * @Route("/test")
 */
class TranslatorController extends Controller
{
    /**
     * @Route("/save_message", name="domis86_translator_save_message")
     */
    public function saveMessageAction(Request $request)
    {
        // TODO: add security

        $messageManager = $this->container->get('domis86_translator.message_manager');
        $messageTranslation = $messageManager->saveMessageTranslationByMessageId(
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
        $webDebugDialog = $this->container->get('domis86_translator.web_debug_dialog');
        return $this->render(
            'Domis86TranslatorBundle:DataCollector:webDebugDialog.html.twig',
            array(
                'webDebugDialog' => $webDebugDialog->getData($location)
                , 'location' => $location
            )
        );
    }

    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        $exampleTranslations = array();
        $exampleTranslations[] = $this->get('translator')->trans('i_dont_exist', array(), 'messages', 'fr');
        $exampleTranslations[] = $this->get('translator')->trans('test_message_1', array(), 'messages', 'fr');
        $exampleTranslations[] = $this->get('translator')->trans('test_message_1', array(), 'messages', 'en');
        $exampleTranslations[] = $this->get('translator')->trans('i_dont_exist_too', array(), 'test2', 'en');
        $exampleTranslations[] = $this->get('translator')->trans('i_exists_only_in_en', array(), 'test3', 'en');

        $exampleTranslations[] = $this->get('translator')->trans('new_test1', array(), 'messages', 'en');
        $exampleTranslations[] = $this->get('translator')->trans('This value should be false.', array(), 'validators', 'en');

        return array('exampleTranslations' => $exampleTranslations);
    }
}
