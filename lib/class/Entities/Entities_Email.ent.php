<?php

Class Entities_Email extends Abstract_Entities
{
   public static $_class_name       = __CLASS__;
   
   protected static $_fields        = Array("email_from_name",
                                            "email_from_email",
                                            "email_to_name",
                                            "email_to_email",
                                            "email_subject",
                                            "email_body", 
                                            "email_text",
                                            "email_type",
                                            "email_reply_email",
                                            "email_return_path",
                                            "email_priority");
   
   public function setEmailFromName($value)
   {
      $this->email_from_name = $value;
      return $this;
   }
   
   public function setEmailFromEmail($value)
   {
      $this->email_from_email = $value;
      return $this;
   }
   
   public function setEmailToName($value)
   {
      $this->email_to_name = $value;
      return $this;
   }
   
   public function setEmailToEmail($value)
   {
      $this->email_to_email = $value;
      return $this;
   }
   
   public function setEmailSubject($value)
   {
      $this->email_subject = $value;
      return $this;
   }
   
   public function setEmailBody($value)
   {
      $this->email_body = $value;
      return $this;
   }
   
   public function setEmailText($value)
   {
      $this->email_text = $value;
      return $this;
   }
   
   public function setEmailReturnPath($value)
   {
      $this->email_return_path = $value;
      return $this;
   }
   
   public function setEmailReplyEmail($value)
   {
      $this->email_reply_email = $value;
      return $this;
   }
   
   public function setEmailPriority($value)
   {
      $this->email_priority = $value;
      return $this;
   }
   
   public function setEmailType($value)
   {
      $this->email_type = $value;
      return $this;
   }
   
   
   public function getEmailFromName(){
      return $this->email_from_name;
   }
   
   public function getEmailFromEmail() {
      return $this->email_from_email;
   }
   
   public function getEmailToName(){
      return $this->email_to_name;
   }
   
   public function getEmailToEmail(){
      return $this->email_to_email;
   }
   
   public function getEmailSubject(){
      return $this->email_subject;
   }
   
   public function getEmailBody(){
      return $this->email_body;
   }
   
   public function getEmailText(){
      return $this->email_text;
   }
   
   public function getEmailReturnPath(){
      return $this->email_return_path;
   }
   
   public function getEmailReplyEmail(){
      return $this->email_reply_email;
   }
   
   public function getEmailPriority(){
      return $this->email_priority;
   }
   
   public function getEmailType(){
      return $this->email_type;
   }
   
   
   
   public function __construct(){
      return $this->init();
   }
}

