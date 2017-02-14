<?php

namespace controllers;

/**
 * Base application controller
 */
class BaseController extends \Application_Controller
{
    /**
     * Gestore db
     * 
     * @var \DAO_DBManager
     */
    protected $database;
    
    public function __construct()
    {
        parent::__construct();
        $this->database = $this->getService('database');
    }
    
    public function __doManipulateActionRequestData(\Application_ActionRequestData $actionRequestData)
    {
        parent::__doManipulateActionRequestData($actionRequestData);
    }
    
    public function __doOnInit()
    {
        parent::__doOnInit();
    }
    
    public function __doOnPostProcess(array $responseAdapted)
    {
        parent::__doOnPostProcess($responseAdapted);
    }
    
    public function __doOnPreProcess(\Application_ActionRequestData $actionRequestData)
    {
        parent::__doOnPreProcess($actionRequestData);
    }
}
