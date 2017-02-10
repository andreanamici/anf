<?php

 /**
  * Nome della cartella che contiene gli oggetti per processare le singole azioni, relativa al proprio package
  */
 define("ACTION_CNT_ACTION_OBJECT_FOLDER_NAME","action");
 
 /**
  * Prefisso dei metodi / action presenti.
  *  
  * Se il portale farà action "login" ricercherò un actionObject dentro la cartella <ACTION_CNT_ACTION_OBJECT_FOLDER_NAME> di nome <ACTION_CNT_ACTION_OBJECT_PREFIX><action>.class.php
  * 
  */
 define("ACTION_CNT_ACTION_OBJECT_PREFIX","Action_");
 
 /**
  * Classe astrata degli ActionObject
  * 
  */
 define("ACTION_CNT_ACTION_OBJECT_ABSTRACT_CLASS","Abstract_ActionObject");
 
 /**
  * Classe basic degli ActionObject, utilizzata se l'action indicata non ha un ActionObject reale
  * 
  */
 define("ACTION_CNT_ACTION_OBJECT_BASIC_CLASS","Basic_ActionObject");
 
 /**
  * Nome Action per action basic
  */
 define("ACTION_CNT_ACTION_OBJECT_BASIC_ACTION_NAME","basic");
 
 
 /**
  * Prefix dei metodi invocati dagli actionObject (method)
  */
 define("ACTION_CNT_ACTION_OBJECT_SUBACTION_METHOD_PREFIX","do");

 /**
  * Azione default 
  */
 define("ACTION_CNT_ACTION_DEFAULT","index");
 