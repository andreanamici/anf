<?php

namespace controllers;

use plugins\FormValidationEngine\Form\FormValidationEngine;

class WelcomeController extends BaseController
{
    /**
     * Metodo che mostra un saluto ad un utente
     * 
     * @param \Application_ActionRequestData $requestData             HttpRequest per il controller
     * @param string                         $name                    Nome, stesso pattern del routing "{name}" => $name
     * @param FormValidationEngine           $form_validation         inietta il servizio automatico di "form_validation"
     * @parma \Application_SessionManager    $session                 inietta il servizio automatico di "session"
     * 
     * @return \Application_ControllerResponseData
     */
    public function helloName(\Application_ActionRequestData $requestData, $name,FormValidationEngine $form_validation, \Application_SessionManager $session)
    {
        $sessionData    = $requestData->getSession();
                
        if($requestData->isMethodPost())        
        {   
            $form_validation->set_rules('name','Nome','required|callback_checkName', 'indicare un nome valido');
            $name = $requestData->getPost()->getIndex('name');
            
            if(!$form_validation->run())
            {
                $session->addFlashMessageError($this->_t('WELCOME_WRONG_DATA'));
            }               
            else
            {
                $session->addFlashMessage($this->_t('WELCOME_CORRECT_DATA', array('{{name}}' => $name)));
            }            
        }
        
        return $this->render('welcome/name',array(
                    'name'              => $name,
                    'currentRoute'      => $this->getApplicationRoutingCurrentRouteData()->getRouteName()
               ));
    }
    
    
    public function doGoodbye(\Application_ActionRequestData $requestData)
    {
        return response('goodbye!');
    }
    
    public function doSayHello(\Application_ActionRequestData $requestData)
    {
        return response('hello!');
    }
    
    public function doEat(\Application_ActionRequestData $requestData)
    {
        
        $this->getService('hooks')->registerHook(function(\Application_HooksData $data){
                
            $data->getKernel()->templating->addTemplate('index');
            
        }, \Interface_HooksType::HOOK_TYPE_PRE_TEMPLATE);
        
        return array(
                    'name'              => '',
                    'date'              => date_now(),
                    'currentRoute'      => $this->getApplicationRoutingCurrentRouteData()->getRouteName()
               );
    }
    
    public function checkName($name)
    {
        return preg_match('/^[A-z\s\']$/',$name);
    }
}

