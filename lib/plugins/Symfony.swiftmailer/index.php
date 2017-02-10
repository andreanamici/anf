<?php

/**
 * Registra il componente di Symfony swiftmailer nei services dell'applicazione 
 */

$kernel      = getApplicationKernel();

$appServices = $kernel->getApplicationServices();
$appConfigs  = $kernel->getApplicationConfigs();
$env         = $kernel->getEnvironment();

$configFilePathByEnv   = $appConfigs->getConfigsFilePath('swiftmailer_'.$env);
$configFilePathDefault = $appConfigs->getConfigsFilePath('swiftmailer');

if(file_exists($configFilePathByEnv))
{
   $appConfigs->loadConfigsFile('swiftmailer_'.$env);
}
else if(file_exists($configFilePathDefault))
{
   $appConfigs->loadConfigsFile('swiftmailer');
}

$mailerType = $appConfigs->getConfigsValue('EMAIL_MESSAGE_MAILER',EMAIL_MAILER_MAIL);

switch($mailerType)
{
    case 'smtp':

        $smtpHost =  $appConfigs->getConfigsValue('EMAIL_MAILER_SMTP_HOST');
        $smtpPort =  $appConfigs->getConfigsValue('EMAIL_MAILER_SMTP_PORT');
        $smtpUser =  $appConfigs->getConfigsValue('EMAIL_MAILER_SMTP_USER');
        $smtpPass =  $appConfigs->getConfigsValue('EMAIL_MAILER_SMTP_PASSWORD');


        $transport = Swift_SmtpTransport::newInstance($smtpHost, $smtpPort)
                                    ->setUsername($smtpUser)
                                    ->setPassword($smtpPass)
                    ;

    case 'sendmail':

        $transport = Swift_SendmailTransport::newInstance('/usr/sbin/sendmail -bs');

    case 'mail':

        $transport = Swift_MailTransport::newInstance();

    break;
}

$mailer = Swift_Mailer::newInstance($transport);

$appServices->registerService('mailer',$mailer);

$appServices->registerService('mailer.message',function($subject = null){
    return Swift_Message::newInstance($subject);
});

$appServices->registerService('mailer.transport',$transport);