<?php

class EntitiesManager_Email extends Abstract_EntitiesManager
{
   
   const EMAIL_PRIORITY_DEFAULT = 1;
   
   const EMAIL_TYPE_MESSAGE = 1;
   const EMAIL_TYPE_NOTIFY  = 2;
   const EMAIL_TYPE_ERROR   = 3;
   
   const PROCESSER_PHPMAILER = 'phpmailer';
   const PROCESSER_MAIL      = 'mail';
   
   private $_email_entity        = null;
   private $_email_entities_list = Array();
   
   private $_email_processer     = null;
   
   /**
    * Manager Email, si occupa di preparare e processare invio di posta tramite diversi processer
    * 
    * @param String $processer Processer posta, default self::<PROCESSER_PHPMAILER>
    * 
    * @return Boolean
    */
   public function __construct($processer = self::PROCESSER_PHPMAILER) {
      return $this->_email_processer = $processer;
   }
   
   /**
    * Prepara l'entità Mail per essere processata
    * 
    * @param String $fromName    Nome mittente
    * @param String $fromEmail   Email mittente
    * @param String $destName    Nome destinatario
    * @param String $destEmail   Email destinatario
    * @param String $subject     Oggetto email
    * @param String $bodyHTML    Body HTML
    * @param String $text        Body formato txt
    * @param Int    $emailType   Tipologia di email, default self::<EMAIL_TYPE_MESSAGE>
    * @param Int    $priority    Priorità posta, default self::<EMAIL_PRIORITY_DEFAULT>
    * 
    * @return Boolean
    */
   public function doPrepareSingleEmail($fromName,$fromEmail,$destName,$destEmail,$subject,$bodyHTML,$text = "",$emailType = self::EMAIL_TYPE_MESSAGE,$priority = self::EMAIL_PRIORITY_DEFAULT)
   {
      if(strlen(trim($bodyHTML))==0){
         return false;
      }
      
      if(strlen(trim($text))==0){
         $text = strip_tags($bodyHTML);
      }
      
      $this->_email_entity = new Entities_Email();
      $this->_email_entity->setEmailType($emailType);
      $this->_email_entity->setEmailFromName($fromName);
      $this->_email_entity->setEmailFromEmail($fromEmail);
      $this->_email_entity->setEmailToName($destName);
      $this->_email_entity->setEmailToEmail($destEmail);
      $this->_email_entity->setEmailSubject($subject);
      $this->_email_entity->setEmailBody($bodyHTML);
      $this->_email_entity->setEmailText($text);
      $this->_email_entity->setEmailPriority($priority);
      $this->_email_entities_list [] = $this->_email_entity;
      
      return true;
   }
   
   /**
    * Prepara piu entità Mail per essere processate, ogni elemento della lista sarò una email processata alla volta
    * 
    * @param String $fromName    Nome mittente
    * @param String $fromEmail   Email mittente
    * @param Array  $destArray   Lista destinatari Array(Array("name"=><string>,"email"=><string>))
    * @param String $subject     Oggetto email
    * @param String $bodyHTML    Body HTML
    * @param String $text        Body formato txt
    * @param Int    $emailType   Tipologia di email, default self::<EMAIL_TYPE_MESSAGE>
    * @param Int    $priority    Priorità posta, default self::<EMAIL_PRIORITY_DEFAULT>
    * 
    * @return Boolean
    */
   public function doPrepareEmailList($fromName,$fromEmail,$destArray,$subject,$bodyHTML,$text,$emailType = self::EMAIL_TYPE_MESSAGE,$priority = self::EMAIL_PRIORITY_DEFAULT)
   {
      if(is_array($destArray) && count($destArray)>0)
      {
         foreach($destArray as $dest)
         {
            if(isset($dest["email"]) && self::checkEmail($dest["email"],false))
            {
               $destName  = isset($dest["name"]) ? $dest["name"] : strstr($dest["email"],"@",true);
               $destEmail = $dest["email"];
               if(!$this->doPrepareSingleEmail($fromName, $fromEmail, $destName, $destEmail, $subject, $bodyHTML, $text)){
                  return $this->throwNewException(3990018888844433,"Impossibile preparare la Mail");
               }
            }
         }
         
         
         return count($this->_email_entities_list)>0;
      }
   }
   
     
   /**
    * Prepara una entità Mail che contiene piu email nel campo "email_dest_email" cosi che verrà spedita una una email contenente piu destinatari in broadcast
    * 
    * @param String $fromName    Nome mittente
    * @param String $fromEmail   Email mittente
    * @param Array  $destArray   Lista destinatari Array(Array("email"=><string>))
    * @param String $subject     Oggetto email
    * @param String $bodyHTML    Body HTML
    * @param String $text        Body formato txt
    * @param Int    $emailType   Tipologia di email, default self::<EMAIL_TYPE_MESSAGE>
    * @param Int    $priority    Priorità posta, default self::<EMAIL_PRIORITY_DEFAULT>
    * 
    * @return Boolean
    */
   public function doPrepareEmailBroadcast($fromName,$fromEmail,$destArray,$subject,$bodyHTML,$text,$emailType = self::EMAIL_TYPE_MESSAGE,$priority = self::EMAIL_PRIORITY_DEFAULT)
   {
      
      if(is_array($destArray) && count($destArray)>0)
      {
         foreach($destArray as $dest)
         {
            if(isset($dest["email"]) && self::checkEmail($dest["email"],false)){
               $destEmailList[]=$dest["email"];
            }
         }
         
         $destEmail = implode(",",$destEmailList);
         
         if(!$this->doPrepareSingleEmail($fromName, $fromEmail,"", $destEmail, $subject, $bodyHTML, $text)){
            return $this->throwNewException(3990018888844433,"Impossibile preparare la Mail");
         }
         
         return count($this->_email_entities_list)>0;
      }
   }
   
   
   /**
    * Invia posta, restituisce il numero di email inviate
    * 
    * @return Int, nr email Processate
    */
   public function doSendEmail()
   {
      if(count($this->_email_entities_list)==0)
      {
         return $this->throwNewException(3993920100049999392005,"Impossibile inviare email! nessun destinatario specificato");
      }
      
      /**
       * Se il sistema è in debug le email vengono girate verso l'indirizzo configurato come forward di debug
       */
      if($this->getApplicationKernel()->isDebugActive())
      { 
         $forwardEmail = $this->getApplicationConfigs()->getConfigsValue('EMAIL_FORWARD_DEBUG');
         
         if($forwardEmail !== false)
         {
            foreach($this->_email_entities_list as $email)
            {
               $email->setEmailToEmail(EMAIL_FORWARD_DEBUG);
            }
         }
      }
      
      switch($this->_email_processer)
      {
         case self::PROCESSER_PHPMAILER:     return $this->_doProcess_PHPMailer();  break;
         
         case self::PROCESSER_MAIL:          return $this->_doProcess_Mail();       break;
      }  
      
      return false;
   }
   
   /**
    * Processa email tramite PHPMailer
    * 
    * @return Int, nr email Processate
    */
   private function _doProcess_PHPMailer()
   {
        $Mail_PHPMailer            = new Mail_PHPMailer(true);
        $Mail_PHPMailer->PluginDir = ROOT_PATH."/lib/class/Mail/";
        $Mail_PHPMailer->Host      = EMAIL_SMTP_HOST;
        $Mail_PHPMailer->Port      = EMAIL_SMTP_PORT;
        $Mail_PHPMailer->CharSet   = strtolower(APPLICATION_TEMPLATING_DEFAULT_CHARSET);
        
        if(EMAIL_SMTP_USE && EMAIL_SMTP_AUTH)
        {
           $Mail_PHPMailer->IsSMTP();
           $Mail_PHPMailer->SMTPAuth   = EMAIL_SMTP_AUTH;
           $Mail_PHPMailer->SMTPSecure = EMAIL_SMTP_ENC;
           $Mail_PHPMailer->Username   = EMAIL_SMTP_USER;
           $Mail_PHPMailer->Password   = EMAIL_SMTP_PASS;
        }
        
        $Mail_PHPMailer->IsHTML(true);
        
        $emailSended = 0;
        
        foreach($this->_email_entities_list as $email)
        {
           $Mail_PHPMailer->AddAddress($email->getEmailToEmail(),$email->getEmailToName());
           $Mail_PHPMailer->SetFrom($email->getEmailFromEmail(),$email->getEmailFromName());
           $Mail_PHPMailer->Subject = EMAIL_SUBJECT_PREFIX. $email->getEmailSubject();
           $Mail_PHPMailer->AltBody = $email->getEmailText();
           $Mail_PHPMailer->Body    = $email->getEmailBody();

           if($Mail_PHPMailer->Send())
           {
              $emailSended++;
              $Mail_PHPMailer->ClearAllRecipients();
              $this->_email_entities_list = Array();
              $this->_email_entity        = null;
           }
        }
        
        return $emailSended;
   }
   
   /**
    * Processa email tramite function mail() php di base, usa localhost
    * 
    * @return Int, nr email Processate
    */
   private function _doProcess_Mail()
   {
      $emailSended = 0;
      
      foreach($this->_email_entities_list as $email)
      {
         
         $headers  = 'MIME-Version: 1.0' . "\r\n";
         $headers .= 'Content-type: text/html; charset=' . APPLICATION_TEMPLATING_DEFAULT_CHARSET. "\r\n";
         $headers .= 'From: '.$email->getEmailFromName().' <'.$email->getEmailFromEmail().'>' . "\r\n";
         
         $to      = $email->getEmailToEmail();
         $subject = EMAIL_SUBJECT_PREFIX . $email->getEmailSubject();
         $message = $email->getEmailBody();
         
         if(mail($to, $subject, $message, $headers)!==false){
            $emailSended++;
         }
      }
      
      $this->_email_entities_list = Array();
      $this->_email_entity        = null;
      
      return $emailSended;
   }
   
   public static function checkEmail($email,$checkdns){
      return Utility_CommonFunction::String_isValidEmail($email,!$checkdns);
   }
}
