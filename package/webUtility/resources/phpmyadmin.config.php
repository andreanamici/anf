<?php

/* Start of servers anframework - phpmyadmin configuration */
@session_name('phpMyAdmin');
@session_start();

$user            = $_SESSION['anframework']['user'];
$password        = $_SESSION['anframework']['password'];
$driver          = $_SESSION['anframework']['driver'];
$dbname          = $_SESSION['anframework']['dbname'];
$host            = $_SESSION['anframework']['host'];

/* Servers configuration */
$i=0;
$i++;

$cfg['Servers'][$i]['verbose']      = $host;
$cfg['Servers'][$i]['host']         = $host;
$cfg['Servers'][$i]['pmadb']        = $dbname;
$cfg['Servers'][$i]['Confirm']      = false;
$cfg['Servers'][$i]['port']         = '';
$cfg['Servers'][$i]['socket']       = '';
$cfg['Servers'][$i]['connect_type'] = 'tcp';
$cfg['Servers'][$i]['extension']    = $driver;
$cfg['Servers'][$i]['auth_type']    = 'cookie';
$cfg['Servers'][$i]['user']         = $user;
$cfg['Servers'][$i]['password']     = $password;
$cfg['Servers'][$i]['only_db']      = $dbname;

$cfg['LoginCookieValidity']  = 3600*24*30;
$cfg['blowfish_secret']      = '51e29fba78d333.95815095';
$cfg['DefaultLang']          = 'it';
$cfg['Lang']                 = 'it';
$cfg['ServerDefault']        = 1;
$cfg['VersionCheck']         = false;
$cfg['SessionSavePath']      = dirname(dirname(__FILE__))."/data/session";
$cfg['TempDir']              = dirname(dirname(__FILE__))."/data/tmp";
$cfg['UploadDir']            = dirname(dirname(__FILE__))."/data/upload";
$cfg['SaveDir']              = dirname(dirname(__FILE__))."/data/savedir";
$cfg['MaxNavigationItems']   = 100;

/* End of servers configuration */