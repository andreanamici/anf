<?php

namespace webProfiler;

/**
 * Questo package gestisce il profiler dell'applicazione
 */
class webProfiler extends \Abstract_Package
{
//   protected $_isEnable = false;
   
   public function getConfigsFileExtension() 
   {
      return self::CONFIGS_FILE_EXTENSION_YAML;
   }
   
   public function isEnable() 
   {
      return $this->getKernelDebugActive();
   }
   
   public function onLoad() 
   {
      parent::onLoad();
      
      $this->getApplicationHooks()->registerHook(new \webProfiler\hooks\Hooks_profiler(),\Interface_HooksType::HOOK_TYPE_SUBSCRIBER);

      $this->getApplicationRouting()->addRoutingMaps(array(
          
                  '_profiler_show' => array(
                    'path'       => '_profiler/status/show',
                    'action'     => 'webProfiler\action\Action_profilerToolbar::setVisible',
                    'controller' => 'ajax'
                  ),
              
                  '_profiler_hide' => array(
                    'path'       => '_profiler/status/hide',
                    'action'     => 'webProfiler\action\Action_profilerToolbar::setHide',
                    'controller' => 'ajax'
                 ),
              
                 '_profiler_command' => array(
                    'path'       => '_profiler/command',
                    'action'     => 'webProfiler\action\Action_profilerToolbar::command',
                    'controller' => 'ajax'
                 ),
                  
                 '_profiler_phpinfo' => array(
                    'path'       => '_profiler/phpinfo',
                    'action'     => 'webProfiler\action\Action_profilerToolbar::phpinfo'
                 ),
                  
                 '_profiler_logstailpanel' => array(
                    'path'       => '_profiler/logstailpanel',
                    'action'     => 'webProfiler\action\Action_logstailpanel',
                    'package'    => 'webProfiler'
                 ),
              
                 '_profiler_logstailpanel_view' => array(
                    'path'       => '_profiler/logstailpanel/view/{logType}',
                    'action'     => 'webProfiler\action\Action_logstailpanel::view',
                    'package'    => 'webProfiler',
                    'params'     => array(
                        'logType' => '(:[string])'
                    )
        )),true);
      
   }
}