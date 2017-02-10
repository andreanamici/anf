<?php

 /**
  * session.name specifies the name of the session which is used as cookie name.
  * It should only contain alphanumeric characters.
  * Defaults to PHPSESSID. 
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.name 
  * @see session_name().
  */
define("SESSION_NAME","PHPSESSID"); 

/**
 *  specifies domain to set in the session cookie
 */
define("SESSION_COOKIE_DOMAIN",SITE_DOMAIN);

/**
 *  specifies path to set in the session cookie. Defaults to /.
 */
define("SESSION_COOKIE_PATH","/");

 /**
  * session.use_cookies specifies whether the module will use cookies to store the session id on the client side.
  * Defaults to 1 (enabled). 
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.use-cookies
  */
define("SESSION_USE_COOKIES",true);

 /**
  * session.use_only_cookies specifies whether the module will only use cookies to store the session id on the client side.
  * Enabling this setting prevents attacks involved passing session ids in URLs.
  * This setting was added in PHP 4.3.0.
  * Defaults to 1 (enabled) since PHP 5.3.0. 
  * 
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.use-only-cookies
  */
define("SESSION_USE_ONLY_COOKIES",true);

 /**
  * session.cookie_secure specifies whether cookies should only be sent over secure connections.
  * Defaults to off. This setting was added in PHP 4.0.4.
  *
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-secure
  * 
  * @see session_get_cookie_params(
  * @see ssession_set_cookie_params(). 
  */
define("SESSION_COOKIE_SECURE",false);

 /**
  * session.cookie_lifetime specifies the lifetime of the cookie in seconds which is sent to the browser.
  * The value 0 means "until the browser is closed." Defaults to 0.
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-lifetime
  * @see session_get_cookie_params() 
  * @see session_set_cookie_params().
  */
define("SESSION_COOKIE_LIFETIME",0);

 /**
  * Marks the cookie as accessible only through the HTTP protocol. This means that the cookie won't be accessible by scripting languages, such as JavaScript.
  * This setting can effectively help to reduce identity theft through XSS attacks (although it is not supported by all browsers).
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.cookie-httponly
  * 
  */
define("SESSION_COOKIE_HTTPONLY",true); 

 /**
  * session.gc_maxlifetime specifies the number of seconds after which data will be seen as 'garbage' and potentially cleaned up.
  * Garbage collection may occur during session start (depending on session.gc_probability and session.gc_divisor).
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.gc-maxlifetime
  */
define("SESSION_GC_MAXLIFETIME",180); 

/**
 * session.gc_probability in conjunction with session.gc_divisor is used to manage probability that the gc (garbage collection) routine is started.
 * Defaults to 1. 
 * @link http://www.php.net/manual/en/session.configuration.php#ini.session.gc-probability
 * See session.gc_divisor for details.
 */
define("SESSION_GC_PROBABILITY",1);

/**
 * session.gc_divisor coupled with session.gc_probability defines the probability that the gc (garbage collection) process is started on every session initialization.
 * The probability is calculated by using gc_probability/gc_divisor, e.g. 1/100 means there is a 1% chance that the GC process starts on each request. 
 * @link http://www.php.net/manual/en/session.configuration.php#ini.session.gc-division
 * session.gc_divisor defaults to 100  
 */
define("SESSION_GC_DIVISION",100);

 /**
  * session.cache_expire specifies time-to-live for cached session pages in minutes, this has no effect for nocache limiter.
  * Defaults to 180.
  * @link http://www.php.net/manual/en/session.configuration.php#ini.session.cache-expire
  * @see also session_cache_expire().
  * 
  */
define("SESSION_CACHE_EXPIRE",0); 

/**
 * session.auto_start specifies whether the session module starts a session automatically on request startup. Defaults to 0 (disabled).
 * @link http://php.net/manual/en/session.configuration.php#ini.session.auto-start
 */
define("SESSION_AUTOSTART",0);

/**
 * session.save_handler defines the name of the handler which is used for storing and retrieving data associated with a session.
 * Defaults to files. Note that individual extensions may register their own save_handlers; registered handlers can be obtained on a per-installation basis by referring to phpinfo().
 * See also session_set_save_handler().
 */
define("SESSION_SAVE_HANDLER","files");

/**
 * Enables upload progress tracking, populating the $_SESSION variable. Defaults to 1, enabled.
 * @link http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.enabled
 */
define("SESSION_UPLOAD_PROGRESS_ENABLED",1);

/**
 * The minimum delay between updates, in seconds. Defaults to "1" (one second).
 * @link http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.enabled
 */
define("SESSION_UPLOAD_PROGRESS_FREQ","1%");

/**
 * Defines how often the upload progress information should be updated. This can be defined in bytes (i.e. "update progress information after every 100 bytes"), or in percentages (i.e. "update progress information after receiving every 1% of the whole filesize"). 
 * Defaults to "1%".
 * @link http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.min-freq
 */
define("SESSION_UPLOAD_PROGRESS_MIN_FREQ","1");

/**
 * The name of the key to be used in $_SESSION storing the progress information. See also session.upload_progress.prefix. 
 * If $_POST[ini_get("session.upload_progress.name")] is not passed or available, upload progressing will not be recorded. 
 * Defaults to "PHP_SESSION_UPLOAD_PROGRESS".
 * http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.name
 */
define("SESSION_UPLOAD_PROGRESS_NAME","PHP_SESSION_UPLOAD_PROGRESS");

/**
 * A prefix used for the upload progress key in the $_SESSION.
 * This key will be concatenated with the value of $_POST[ini_get("session.upload_progress.name")] to provide a unique index.
 * Defaults to "upload_progress_".
 * http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.prefix
 */
define("SESSION_UPLOAD_PROGRESS_PREFIX","upload_progress_");


/**
 * Cleanup the progress information as soon as all POST data has been read (i.e. upload completed).
 * Defaults to 1, enabled.
 * @link http://php.net/manual/en/session.configuration.php#ini.session.upload-progress.cleanup
 */
define("SESSION_UPLOAD_PROGRESS_CLEANUP",0);

/**
 *session.save_path defines the argument which is passed to the save handler. If you choose the default files handler, this is the path where the files are created. 
 * There is an optional N argument to this directive that determines the number of directory levels your session files will be spread around in. 
 * For example, setting to '5;/tmp' may end up creating a session file and location like /tmp/4/b/1/e/3/sess_4b1e384ad74619bd212e236e52a5a174If . In order to use N you must create all of these directories before use.
 * A small shell script exists in ext/session to do this, it's called mod_files.sh, with a Windows version called mod_files.bat. Also note that if N is used and greater than 0 then automatic garbage collection will not be performed, see a copy of php.ini for further information. 
 * Also, if you use N, be sure to surround session.save_path in "quotes" because the separator (;) is also used for comments in php.ini. 
 * 
 * @see session_save_path();
 * @link http://www.php.net/manual/en/session.configuration.php#ini.session.save-path
 * 
 * es: define("SESSION_SAVE_PATH",ROOT_PATH."/var/sessions");
 */
define("SESSION_SAVE_PATH",ini_get('session.save_path'));
