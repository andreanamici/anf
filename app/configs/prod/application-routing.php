<?php

require_once 'application.php';

/**
 * Shortcuts aggiuntivi ai validatori
 */
define("APPLICATION_ROUTING_SHORTCUTS",serialize(Array(
    '(:[lang])'         => '([a-z]{2})',
    '(:lang)'           => '(?<lang>[a-z]{2})',
    '(:[locale])'       => '([a-z]{2}\_[A-Z]{2})',
    '(:locale)'         => '(?<locale>[a-z]{2}\_[A-Z]{2})',
)));


/**
 * Definisce le rotte che verranno utilizzate dal Kernel per smistare le action con le relative method
 * 
 * Esempio di una rotta:
 * 
 * Base:
 * 
 *         '_main_page'   =>  Array(    'path'         => '/',
 *                                      'action'       => 'main'
 *                             ),
 * 
 * Avanzata:
 * 
 *   - Questa rotta è sufficiente per girare tutte le chiamate con il prefisso / verso l'ActionObject indicato nell'action , invocandone il relativo metodo
 * 
 *   - Questa rotta sarà valida solamente se il sottodominio sarà validato dallo shortcut registarto "(:[myshortcutvalidator])".
 * 
 *   - Questa rotta verrà generata con i valori indicati al parametri "subdomain" o se omesso (deve essere presente la chiave 'defaults') verrà ricercato in session un valore tra quelli indicati a cascata,
 *     generando un eccezione qualora questi non siano presenti.
 *  
 *     Eventuali parametri non riconosciuti, passati alla rotta in fase di generazione, verranno appesi come Query String all'url generato.
 * 
 * 
 *         '_articles'     => Array(     
 *                                    'path'         => '(:action)/(:method)',
 *                                    'host'         => '{subdomain}.miosito.it,
 *                                    'action'       => '{action}',
 *                                    'method'       => '{method}',
 *                                    'controller'   => 'html'
 *                                    'package'      => 'web-article'
 *                                    'params'       => Array(
 *                                                         'subdomain' => '(:[myshortcutvalidator])'
 *                                                   ),  
 *                                    'defaults'     => Array(                                               
 *                                                         'subdomain' =>   Array('@session.subdomain.test',
 *                                                                                '@session.subdomain.dev')
 *                                                      ),
 *                            )
 * 
 *     Questo url: http://test.ilmiosito.it/article
 *                 Processerà il package 'web-article', ricercano l'actionObject 'Action_article', richiamandone il metodo Action_article::doProcessMe() con una response HTML
 * 
 *     Questo url: http://test.ilmiosito.it/article/view/
 *                 Processerà il package 'web-article', ricercano l'actionObject 'Action_article', richiamandone il metodo Action_article::doView() con una response HTML
 * 
 * NB: Default Routing (fai attenzione prima di editare)
 * 
 */
define("APPLICATION_ROUTING",serialize(require_once APPLICATION_CORE_PATH . '/configs/application-core-routing.php'));
