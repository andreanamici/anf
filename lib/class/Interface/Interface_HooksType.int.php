<?php

/**
 * Interfaccia per le tipologie di hooks
 */
interface Interface_HooksType
{
   /**
    * Hook lanciato al termine dell'inizializzazione del kernel
    */
   const HOOK_TYPE_KERNEL_LOAD          = 'kernel.load';
   
   /**
    * Hook lanciato prima del caricamento degli hooks nel kernel, verrà passata la lista dei packages da caricare
    */
   const HOOK_TYPE_PRE_PACKAGE     = 'prepackages';
   
   /**
    * Hook lanciato subito dopo il caricamento di tutti i package nel kernel
    */
   const HOOK_TYPE_POST_PACKAGE    = 'postpaclage';
   
   /**
    * Hook lanciato appena prima dell'elaborazione del routing
    */
   const HOOK_TYPE_PRE_ROUTING           = 'prerouting';
   
   /**
    * Hook lanciato subito dopo  l'elaborazione del routing
    */
   const HOOK_TYPE_POST_ROUTING          = 'postrouting';
   
   /** 
    * Hook invocato subito dopo la generazione di un url, può modificare il valore dell'url generato
    */
   const HOOK_TYPE_ROUTING_POST_URL      = 'routing.post_url';
   
   /**
    * Hook invocato prima della generazione di un url, può modificare i valori della rotta matchata
    */
   const HOOK_TYPE_ROUTING_PRE_URL       = 'routing.pre_url';
   
   /**
    * Hook lanciato prima di invocare l'ActionController determinato dal Routing
    */
   const HOOK_TYPE_PRE_CONTROLLER        = 'precontroller';
   
   /**
    * Hook lanciato subito dopo l'inizializzazione del controller, è possibile invocare un ActionController diverso e passarla all'Hook
    */
   const HOOK_TYPE_POST_CONTROLLER       = 'postcontroller';
   
   /**
    * Hook lanciato prima di invocare l'actionObject
    */
   const HOOK_TYPE_PRE_ACTION             = 'preaction';
   
   /**
    * Hook lanciato prima di invocare il metodo dell'actionObject
    */
   const HOOK_TYPE_PRE_ACTION_METHOD      = 'preactionmethod';
   
   /**
    * Hook lanciato subito dopo la response dell'actionObject
    */
   const HOOK_TYPE_POST_ACTION            = 'postaction';
   
   /**
    * Hook lanciato prima del Render del template con il template engine impostato
    */
   const HOOK_TYPE_PRE_TEMPLATE           = 'pretemplate';
   
   /**
    * Hook lanciato subito dopo il render del template, ad output rilasciato
    */
   const HOOK_TYPE_POST_TEMPLATE          = 'posttemplate';
   
   /**
    * Hook lanciato subito prima del rendering finale della response, è disponibile qui la response String e gli headers
    */
   const HOOK_TYPE_PRE_RESPONSE           = 'preresponse';
   
   /**
    * Hook lanciato prima di effettuare il redirect, verrà passato come dato l'url puntato
    */
   const HOOK_TYPE_PRE_REDIRECT          = 'preredirect';
   
   /**
    * Hook lanciato alla chiusura del kernel
    */
   const HOOK_TYPE_KERNEL_END              = 'kernel.end';
   
   /**
    * Hook lanciato in cado di exception cattuarate dal Kernel
    */
   const HOOK_TYPE_EXCEPTION               = 'exception';
   
   /**
    * Hook lanciato alla registrazione della session, permette di poter modificare il gestore della Sessione
    * @see \Application_SessionManager
    */
   const HOOK_TYPE_SESSION_REGISTER        = 'session.register';
   
   /**
    * Hook lanciato in caso di sessione scaduta
    */
   const HOOK_TYPE_SESSION_EXPIRE          = 'session.expire';   
   
   /**
    * Hook lanciato in caso di pulizia dei dati di cache
    */
   const HOOK_TYPE_CACHE_CLEAR             = 'cache.clear';

   /**
    * Hook lanciato quando l'applicationConfigs tenta di caricare una configurazione di un file non riconosciuto
    */
   const HOOK_TYPE_CONFIG_LOAD             = 'config.load';
   
   /**
    * Hook lanciato quando l'applicationLanguages tenta di caricare un file di locale sconosciuto, la cui estenzione non è tra quelle riconosciute nativamente dal framework
    */
   const HOOK_TYPE_LOCALE_LOAD             = 'locale.load';
   
   /**
    * Hook lanciato prima delle traduzioni, permette di modificare i parmetri utilizzati per le traduzioni
    */
   const HOOK_TYPE_LOCALE_TRANSLATE_BEFORE = 'locale.translate.before';
   
   /**
    * Hook lanciato se vi sono hook registrati a questa tipologia al posto dell'attuale sistema di traduzione, deve restituire la stringa tradotta in base i parametri fornit in HookData
    */
   const HOOK_TYPE_LOCALE_TRANSLATE_TRANS  = 'locale.translate.trans';
   
   /**
    * Hook lanciato subito dopo la traduzione
    */
   const HOOK_TYPE_LOCALE_TRANSLATE_AFTER  = 'locale.translate.after';
   
   /**
    * Hook lanciato quando il form fallisce il controllo del csrf token
    */
   const HOOK_TYPE_FORM_CSRF_EXPIRED  = 'form.csrf_expired';
   
   /**
    * Questa tipologia di hooks indica che sarà presente una configurazione specifica nella classe hook dichiarata attraverso il metodo getSubscriberConfiguration()
    */
   const HOOK_TYPE_SUBSCRIBER              = 'subscriber';
   
   /**
    * Estenzione file hooks
    */
   const HOOK_FILE_EXTENSION     = APPLICATION_HOOKS_FILE_EXTENSION;
   
   /**
    * Directory di default degli hooks
    */
   const HOOK_DEFAULT_DIRECTORY  = APPLICATION_HOOKS_DEFAULT_DIRECTORY;
      
   /**
    * Nome del metodo dell'hook di default
    */
   const HOOK_DEFAULT_METHOD     = APPLICATION_HOOKS_MAIN_METHOD_NAME;
   
   /**
    * Status hook attivo
    */
   const HOOK_STATUS_ENABLE      = 1;
   
   /**
    * Status hook disattivo
    */
   const HOOK_STATUS_DISABLE      = 0;
   
   /**
    * Priorità minima dell'hook
    */
   const HOOK_PRIORITY_MIN = 0;
   
   /**
    * Priorità di esecuzuine massima dell'hook
    */
   const HOOK_PRIORITY_MAX = 250;
   
   /**
    * Indica il livello di profondità dell'esecuzione dell'hook
    */
   const HOOK_SUBLEVEL = 1;
}
