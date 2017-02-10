<?php

namespace plugins\FormValidationEngine\Form;

/**
 * Questa classe contiene le rules di validazione
 */
class FormValidationRules
{
    /**
     * Dati
     * @var Array
     */
    protected $_field_data = array();
    
    /**
     * Database Manager
     * @var DAO_DBManager
     */
    protected $_database   = null;
    
    /**
     * Security class form
     * @var FormValidationSecurity
     */
    protected $_validation_security;
    
    /**
     * Adult date
     * @var Int
     */
    protected $_adult_year = 18;
		
    public function __construct(array $data = array())
    {
        $this->_field_data          = $data;
        $this->_database            = getApplicationService('database');
        $this->_validation_security = new FormValidationSecurity();
    }
    
    /**
     * Set field data 
     * 
     * @param array $data
     * 
     * @return \plugins\FormValidationEngine\Form\FormValidationRules
     */
    public function set_field_data(array $data)
    {
        $this->_field_data = $data;
        return $this;
    }
        
    /**
     * Required
     *
     * @param	string
     * @return	bool
     */
    public function required($str)
    {
        return is_array($str) ? (bool) count($str) : (trim($str) !== '');
    }

    // --------------------------------------------------------------------

    /**
     * Performs a Regular Expression match test.
     *
     * @param	string
     * @param	string	regex
     * @return	bool
     */
    public function regex_match($str, $regex)
    {
        return (bool) preg_match($regex, $str);
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @param	string	$str	string to compare against
     * @param	string	$field
     * @return	bool
     */
    public function matches($str, $field)
    {
        return isset($this->_field_data[$field], $this->_field_data[$field]['postdata']) ? ($str === $this->_field_data[$field]['postdata']) : false;
    }

    // --------------------------------------------------------------------

    /**
     * Differs from another field
     *
     * @param	string
     * @param	string	field
     * @return	bool
     */
    public function differs($str, $field)
    {
        return !(isset($this->_field_data[$field]) && $this->_field_data[$field]['postdata'] === $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is Unique
     *
     * Check if the input value doesn't already exist
     * in the specified database field.
     *
     * @param	string	$str
     * @param	string	$field
     * @return	bool
     */
    public function is_unique($str, $field)
    {
        sscanf($field, '%[^.].%[^.]', $table, $field);        
        return $this->_database->getFluentPDO()
                    ->from($table)
                    ->where($field.' =:field',array('field'=>$str))
                    ->execute()
                    ->rowCount() == 0;
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public function min_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return false;
        }

        return (MB_ENABLED === true) ? ($val <= mb_strlen($str)) : ($val <= strlen($str));
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public function max_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return false;
        }

        return (MB_ENABLED === true) ? ($val >= mb_strlen($str)) : ($val >= strlen($str));
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public function exact_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return false;
        }

        return (MB_ENABLED === true) ? (mb_strlen($str) === (int) $val) : (strlen($str) === (int) $val);
    }

    // --------------------------------------------------------------------

    /**
     * Valid URL
     *
     * @param	string	$str
     * @return	bool
     */
    public function valid_url($str)
    {
        return (filter_var($str, FILTER_VALIDATE_URL) !== FALSE);
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @param	string
     * @return	bool
     */
    public function valid_email($str)
    {
        if (function_exists('idn_to_ascii') && $atpos = strpos($str, '@'))
        {
            $str = substr($str, 0, ++$atpos) . idn_to_ascii(substr($str, $atpos));
        }

        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    // --------------------------------------------------------------------

    /**
     * Valid Emails
     *
     * @param	string
     * @return	bool
     */
    public function valid_emails($str)
    {
        if (strpos($str, ',') === false || strpos($str, ';') === false)
        {
            return $this->valid_email(trim($str));
        }

        if(strpos($str, ',') || strpos($str, ';'))
        {
            $glue = strpos($str, ',') ? ',' : ';';
            
            foreach (explode($glue, $str) as $email)
            {
                if (trim($email) !== '' && $this->valid_email(trim($email)) === false)
                {
                    return false;
                }
            }
        }
        
        return true;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address
     *
     * @param	string
     * @param	string	'ipv4' or 'ipv6' to validate a specific IP format
     * @return	bool
     */
    public function valid_ip($ip, $which = '')
    {
        switch (strtolower($which))
        {
                case 'ipv4':
                        $which = FILTER_FLAG_IPV4;
                        break;
                case 'ipv6':
                        $which = FILTER_FLAG_IPV6;
                        break;
                default:
                        $which = NULL;
                        break;
        }

        return (bool) filter_var($ip, FILTER_VALIDATE_IP, $which);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @param	string
     * @return	bool
     */
    public function alpha($str)
    {
        return ctype_alpha($str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @param	string
     * @return	bool
     */
    public function alpha_numeric($str)
    {
        return ctype_alnum((string) $str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric w/ spaces
     *
     * @param	string
     * @return	bool
     */
    public function alpha_numeric_spaces($str)
    {
        return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @param	string
     * @return	bool
     */
    public function alpha_dash($str)
    {
        return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @param	string
     * @return	bool
     */
    public function numeric($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @param	string
     * @return	bool
     */
    public function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Decimal number
     *
     * @param	string
     * @return	bool
     */
    public function decimal($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Greater than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public function greater_than($str, $min)
    {
        return is_numeric($str) ? ($str > $min) : false;
    }

    // --------------------------------------------------------------------

    /**
     * Equal to or Greater than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public function greater_than_equal_to($str, $min)
    {
        return is_numeric($str) ? ($str >= $min) : false;
    }

    // --------------------------------------------------------------------

    /**
     * Less than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public function less_than($str, $max)
    {
        return is_numeric($str) ? ($str < $max) : false;
    }

    // --------------------------------------------------------------------

    /**
     * Equal to or Less than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public function less_than_equal_to($str, $max)
    {
        return is_numeric($str) ? ($str <= $max) : false;
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public function is_natural($str)
    {
        return ctype_digit((string) $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public function is_natural_no_zero($str)
    {
        return ($str != 0 && ctype_digit((string) $str));
    }

    // --------------------------------------------------------------------

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param	string
     * @return	bool
     */
    public function valid_base64($str)
    {
        return (base64_encode(base64_decode($str)) === $str);
    }

    // --------------------------------------------------------------------

    /**
     * Prep data for form
     *
     * This function allows HTML to be safely shown in a form.
     * Special characters are converted.
     *
     * @param	string
     * @return	string
     */
    public function prep_for_form($data = '')
    {
        if ($this->_safe_form_data === false OR empty($data))
        {
            return $data;
        }

        if (is_array($data))
        {
            foreach ($data as $key => $val)
            {
                $data[$key] = $this->prep_for_form($val);
            }

            return $data;
        }

        return str_replace(array("'", '"', '<', '>'), array('&#39;', '&quot;', '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    /**
     * Prep URL
     *
     * @param	string
     * @return	string
     */
    public function prep_url($str = '')
    {
        if ($str === 'http://' OR $str === '')
        {
            return '';
        }

        if (strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0)
        {
            return 'http://' . $str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @param	string
     * @return	string
     */
    public function strip_image_tags($str)
    {
        return preg_replace(array('#<img[\s/]+.*?src\s*=\s*["\'](.+?)["\'].*?\>#', '#<img[\s/]+.*?src\s*=\s*(.+?).*?\>#'), '\\1', $str);
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * Sanitizes data so that Cross Site Scripting Hacks can be
     * prevented.  This method does a fair amount of work but
     * it is extremely thorough, designed to prevent even the
     * most obscure XSS attempts.  Nothing is ever 100% foolproof,
     * of course, but I haven't been able to get anything passed
     * the filter.
     *
     * Note: Should only be used to deal with data upon submission.
     *	 It's not something that should be used for general
     *	 runtime processing.
     *
     * @link	http://channel.bitflux.ch/wiki/XSS_Prevention
     * 		Based in part on some code and ideas from Bitflux.
     *
     * @link	http://ha.ckers.org/xss.html
     * 		To help develop this script I used this great list of
     *		vulnerabilities along with a few other hacks I've
     *		harvested from examining vulnerabilities in other programs.
     *
     * @param	string|string[]	$str		Input data
     * @param 	bool		$is_image	Whether the input is an image
     * @return	string
     */
    public function xss_clean($str,$is_image = false)
    {
        return $this->_validation_security->xss_clean($str,$is_image);
    }
    
    
    /**
     * Trim a value
     * 
     * @param string $val
     * 
     * @return string
     */
    public function trim($val)
    {
        return trim($val);
    }
    
    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @param	string
     * @return	string
     */
    public function encode_php_tags($str)
    {
        return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
    }
    
   /**
    * Verifica se la stringa contiene numeri
    *
    * @access	public
    * @param	string $str stringa da verificare
    * @return	boolean
    */	 
    public function contains_numbers($str)
    {
            return (preg_match('#[0-9]#',$str)) ? TRUE : FALSE;
    }

    /**
     * Verifica se la stringa contiene caratteri
     *
     * @access	public
     * @param	string $str stringa da verificare
     * @return	boolean
     */
    public function contains_chars($str)
    {
            return (preg_match('/[a-zA-Z]/',$str)) ? TRUE : FALSE;
    }


    public function valid_email_username($value)
    {
        return preg_match('/^[A-z0-9\.\-\_\+]+$/',$value) ? TRUE : FALSE;
    }

    /**
    * Verifica che la password abbia la lunghezza minima specificata e
    * contenga caratteri e numeri 
    *
    * @access public
    * @param string 	$pwd password da verificare
    * @param integer 	$size lunghezza minima della password
    * 
    * @return boolean
    * @author Laura
    */
    public function valid_password($pwd, $size)
    {	
            if ($pwd == '')
            {
                    return TRUE;
            }	

            if (strlen($pwd) < $size)
            {
                    return FALSE;
            }

            if (preg_match('/^[a-zA-Z0-9]+$/', $pwd))
            {
                    return TRUE;
            }	

            return FALSE;
    }

    /**
    * Verifica che la stringa sia un nome/cognome, ovvero contenga solo 
    * caratteri alfabetici, spazi o apostrofi
    *
    * @access public
    * @param string 	$str stringa da verificare
    * 
    * @return boolean
    * @author Fabrizio
    */
    public function valid_name($str)
    {
            return (preg_match("/^([A-Za-zàèéìòù]{1,}[ ']{0,1})+$/", $str)) ? TRUE : FALSE;
    }

    /**
     * Verifica se il nome del file è sintatticamente valido.
     * Non può contenere i seguenti caratteri: / ? * : ; { } \ %
     *
     * @param string $str. La stringa da verificare.
     * @return boolean
     */
    public function valid_filename($str)
    {
            return preg_match("/^[^\\/?*:;{}%\\\\]+\\.[^\\/?*:;{}\\\\]{3,4}$/", $str) ? TRUE : FALSE;
    }


    /**
     * Verifica che il numero di telefono sia valido. 
     * 
     * @access public
     * @param string	$phone numero da verificare
     * 
     * @return boolean
    */
    public function valid_phone($phone)
    {
            return (preg_match("/^(\+)?([0-9]{1,}[\/\-]{0,1})+$/", $phone)) ? TRUE : FALSE;
    } 

    /**
     * Verifica che lo zip code sia valido in base alla regola fornita. 
     * 
     * @access public
     * @param string	$code valore da verificare
     * @param string	$regex regola per la verifica
     * 
     * @return boolean
    */
    public function valid_zip_code($code, $regex)
    {
            return (preg_match($regex, $code)) ? TRUE : FALSE;
    }

    /**
     * Verifica la validità del codice fiscale
     * 
     * @access public
     * @param string	$tax_code valore da verificare
     * @param string	$regex regola per la verifica
     * 
     * @return boolean
    */
    function valid_tax_code($tax_code)
    {
        if($tax_code === '')
        {
            return false;
        }

        if(strlen($tax_code) != 16)
        {
            return false;
        }

        $tax_code = strtoupper($tax_code);

        if( preg_match("/^[A-Z0-9]+\$/", $tax_code) != 1 )
        {
            return false;
        }

        $s = 0;

        for($i = 1; $i <= 13; $i += 2)
        {
            $c = $tax_code[$i];
            if( strcmp($c, "0") >= 0 and strcmp($c, "9") <= 0 )
                $s += ord($c) - ord('0');
            else
                $s += ord($c) - ord('A');
        }

        for($i = 0; $i <= 14; $i += 2)
        {
            $c = $tax_code[$i];
            switch($c)
            {
                case '0':  $s += 1;   break;
                case '1':  $s += 0;   break;
                case '2':  $s += 5;   break;
                case '3':  $s += 7;   break;
                case '4':  $s += 9;   break;
                case '5':  $s += 13;  break;
                case '6':  $s += 15;  break;
                case '7':  $s += 17;  break;
                case '8':  $s += 19;  break;
                case '9':  $s += 21;  break;
                case 'A':  $s += 1;   break;
                case 'B':  $s += 0;   break;
                case 'C':  $s += 5;   break;
                case 'D':  $s += 7;   break;
                case 'E':  $s += 9;   break;
                case 'F':  $s += 13;  break;
                case 'G':  $s += 15;  break;
                case 'H':  $s += 17;  break;
                case 'I':  $s += 19;  break;
                case 'J':  $s += 21;  break;
                case 'K':  $s += 2;   break;
                case 'L':  $s += 4;   break;
                case 'M':  $s += 18;  break;
                case 'N':  $s += 20;  break;
                case 'O':  $s += 11;  break;
                case 'P':  $s += 3;   break;
                case 'Q':  $s += 6;   break;
                case 'R':  $s += 8;   break;
                case 'S':  $s += 12;  break;
                case 'T':  $s += 14;  break;
                case 'U':  $s += 16;  break;
                case 'V':  $s += 10;  break;
                case 'W':  $s += 22;  break;
                case 'X':  $s += 25;  break;
                case 'Y':  $s += 24;  break;
                case 'Z':  $s += 23;  break;
            }
        }

        if( chr($s%26 + ord('A')) != $tax_code[15] )
        {
            return false;
        }

        return true;
    }

    /**
     * Verifica la validità della partita IVA
     * 
     * @access public
     * @param string	$code valore da verificare
     * @param string	$regex regola per la verifica
     * 
     * @return boolean
    */
    public function valid_vat_number($code,$regex = NULL)
    {
            $regex = strlen($regex) > 0 ? $regex : '^[0-9]{11}$';
            return preg_match('/'.$regex.'/',$code) ? TRUE : FALSE;
    }

    /**
     * Verifica che il value indicato sia una partita IVA valida o un codice fiscale
     * 
     * @access public
     * @param string	$code valore da verificare
     * @param string	$regex regola per la verifica
     * 
     * @return boolean
    */
    public function valid_tax_code_or_vat_number($value)
    {
        if(!$this->valid_tax_code($value) && !$this->valid_vat_number($value))
        {
           return FALSE;
        }

        return TRUE;
    }


    public function valid_price($value)
    {
        return preg_match("/^[0-9]{1,}\.[0-9]{1,}$/",$value) || 
               preg_match("/^[0-9]{1,}\,[0-9]{1,}$/",$value) || 
               preg_match("/^[0-9]+$/",$value)               || 
               preg_match("/^[0-9]{1,}.[0-9]{3}$/",$value) ||
               preg_match("/^[0-9]{1,}.[0-9]{3}\,[0-9]{1,3}+$/",$value);
    }

    /**
     * Verifica che l'id selezionato sia non vuoto e maggiore di zero.
     * 
     * @access public
     * @param string	$id id da verificare
     * 
     * @return boolean
    */
    public function valid_id($id)
    {
            return ( ! empty($id) && $id > 0);
    }

    /**
     * Verifica che il valore inserito sia numerico.  
     * 
     * @access public
     * @param string	$value valore da verificare.
     * 
     * @return boolean
    */
    public function valid_decimal_number($value)
    {
            return (
                    preg_match("/^[0-9]{1,}(\.[0-9]{3})*(,[0-9]+)?$/", $value) 
                    ||
                    preg_match("/^[0-9]{1,}(,[0-9]{3})*(\.[0-9]+)?$/", $value)
             ) ? TRUE : FALSE;
    }

    /**
     * Verifica che l'etichetta contenga solo caratteri non accentati,
     * numeri e il trattino (-).  
     * 
     * @access public
     * @param string	$value valore da verificare.
     * 
     * @return boolean
    */
    public function valid_label($value)
    {
            return (preg_match("/^[0-9a-zA-Z-]{1,}?$/", $value)) ? TRUE : FALSE;
    }

    /**
     * Real URL
     *
     * @access    public
     * @param    string
     * @return    string
     */
    function valid_real_url($url)
    {
        $array  = get_headers($url);

        if(strpos($array[0],"200"))
        {
            return TRUE;
        }

        return FALSE;
    } 

    public function valid_youtube_url($value)
    {
            if ($value == '')
            {
                    return TRUE;
            }	

            if (preg_match('/www\.youtube\.com\/(embed)\/[a-z0-9\-_]+$/i', $value) OR preg_match('/www\.youtube\.com\/(watch)\?v=[a-z0-9\-_]+$/i', $value) OR preg_match('/youtu\.be\/[a-z0-9\-_]+$/i', $value))
            {
                    return TRUE;
            }	
            return FALSE;
    }	

    public function valid_date($value)
    {		
            if ($value == '')
            {
                    return TRUE;
            }	

            if (preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/i', $value))
            {
                    $array = explode('/',$value);
                    if (checkdate($array[1], $array[0], $array[2]))
                    {
                            return TRUE;
                    }	
                    return FALSE;
            }
            elseif(preg_match('/[0-9]{2}\/[0-9]{2}\/[0-9]{2}$/i', $value))
            {
                    $array = explode('/',$value);
                    if (checkdate($array[1], $array[0],'20'.$array[2]))
                    {
                            return TRUE;
                    }		
                    return FALSE;			
            }	
            elseif(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}$/i', $value))
            {
                    $array = explode('-',$value);
                    if (checkdate($array[1], $array[2], $array[0]))
                    {
                            return TRUE;
                    }	
                    return FALSE;			
            }		
            elseif(preg_match('/[0-9]{4}-[0-9]{2}-[0-9]{2}$/i', $value))
            {
                    $array = explode('-',$value);
                    if (checkdate($array[1], $array[2], '20'.$array[0]))
                    {
                            return TRUE;
                    }	
                    return FALSE;			
            }		

            return FALSE;
    }

    public function valid_date_adult($value)
    {
            if(!$this->valid_date($value))
            {
                return FALSE;
            }

            list($day,$month,$year) = explode("/",$value);

            $day   = intval($day);
            $month = intval($month);
            $year  = intval($year);

            $date_sql  = $year.'-'.$month.'-'.$day;
            $timestamp = strtotime('-'.$this->_adult_year.' year',time());

            if($timestamp <= strtotime($date_sql))
            {
                return FALSE;
            }

            return TRUE;
    }


    /**
     * Valida due date, analizzando il range temporale, partendo dalla data iniziale.
     * 
     * @param mixed  $todate                  data finale
     * @param miixed $fromdate_or_input_name  data iniziale / nome del campo html della data finale 
     * @param bool   $equal                   indica se le date possono coincidere, default TRUE
     * @param string $fromdate_input_name     nome del campo html della data finale
     * 
     * @return boolean
     */
    public function valid_date_range($todate,$fromdate_or_input_name,$equal = TRUE, $fromdate_input_name = NULL)
    {            
        if(strlen($todate) == 0)
        {
            return TRUE;
        }

        if(strlen($fromdate_or_input_name) == 0)
        {
            return FALSE;
        }


        //Check date

        if(date_is_valid($todate) && date_is_valid($fromdate_or_input_name))    //controllo le date
        {
            $fromdate = $fromdate_or_input_name;

            if($equal)  //Le date possono coincidere
            {
                if(strtotime($todate) < strtotime($fromdate))
                {
                    if($fromdate_input_name != NULL)
                    {
                        $this->manual_set_error_message($fromdate_input_name,sprintf($this->_messages_translation_array['valid_date_range'],$fromdate_input_name));
                    }

                    return FALSE;
                }                    
            }
            else //Le date non possono coincidere
            {
                if(strtotime($fromdate) >= strtotime($todate))
                {
                    if($fromdate_input_name != NULL)
                    {
                        $this->manual_set_error_message($fromdate_input_name,sprintf($this->_messages_translation_array['valid_date_range'],$fromdate_input_name));
                    }

                    return FALSE;
                }
            }

            return TRUE;
        }
        else if($fromdate_input_name == NULL) //Preparo il controllo delle date, ho passato come 2 argomento il nome del campo html del fromdate
        {                         
            $fromdate_input_name = $fromdate_or_input_name;
            $fromdate            = set_value($fromdate_input_name,false); 

            if(!$fromdate)
            {
                $fromdate = date("Y-m-d");
            }

            if(!date_is_valid($todate))
            {
                $todate = date_to_sql($todate);
            }

            if(!date_is_valid($fromdate))
            {
                $fromdate = date_to_sql($fromdate);
            }            

            if(!$todate)
            {
                return FALSE;
            }

            if(!$fromdate)
            {
                return FALSE;
            }

            return $this->valid_date_range($todate, $fromdate, $equal,$fromdate_input_name);
        }

        return FALSE;
    }

    public function date_greater_than($value,$date)
    {
        if(empty($value))
        {
            return TRUE;
        }

        $orig_date = $date;

        $date  = date_is_valid($date)  ? $date  : date_to_sql($date);
        $value = date_is_valid($value) ? $value : date_to_sql($value);   

        if(date_is_valid($date) && date_is_valid($value))
        {
                return strtotime($value . ' 00:00:00') > strtotime($date . ' 00:00:00');
        }
        else
        {
            $date_field = set_value($orig_date,FALSE);

            if($date_field !== FALSE)
            {
                return $this->date_greater_than($value, $date_field);
            }
        }

        return FALSE;
    }

    public function date_greater_equal_than($value,$date)
    {
        if(empty($value))
        {
            return TRUE;
        }

        $orig_date = $date;

        $date  = date_is_valid($date)  ? $date  : date_to_sql($date);
        $value = date_is_valid($value) ? $value : date_to_sql($value);

        if(date_is_valid($date) && date_is_valid($value))
        {
            return strtotime($value. ' 00:00:00') >= strtotime($date.' 00:00:00');
        }
        else
        {
            $date_field = set_value($orig_date,FALSE);

            if($date_field !== FALSE)
            {
                return $this->date_greater_equal_than($value, $date_field);
            }
        }

        return FALSE;
    }

    public function date_less_than($value,$date)
    {
        if(empty($value))
        {
            return TRUE;
        }

        $orig_date = $date;

        $date  = date_is_valid($date)  ? $date  : date_to_sql($date);
        $value = date_is_valid($value) ? $value : date_to_sql($value);

        if(date_is_valid($date) && date_is_valid($value))
        {
            return strtotime($value . ' 00:00:00') < strtotime($date . ' 00:00:00');
        }
        else
        {
            $date_field = set_value($orig_date,FALSE);

            if($date_field !== FALSE)
            {
                return $this->date_less_than($value, $date_field);
            }
        }

        return FALSE;
    }

    public function date_less_equal_than($value,$date)
    {
        if(empty($value))
        {
            return TRUE;
        }

        $orig_date = $date;

        $date  = date_is_valid($date)  ? $date  : date_to_sql($date);
        $value = date_is_valid($value) ? $value : date_to_sql($value);

        if(date_is_valid($date) && date_is_valid($value))
        {
            return strtotime($value . ' 00:00:00') <= strtotime($date . ' 00:00:00');
        }
        else
        {
            $date_field = set_value($orig_date,FALSE);

            if($date_field !== FALSE)
            {
                return $this->date_less_equal_than($value, $date_field);
            }
        }

        return FALSE;
    }

    public function greater_than_or_equal($value,$greater_than_value)
    {
        return $greater_than_value<=$value;
    }

    public function less_than_or_equal($value,$less_than_value)
    {
        return $less_than_value>=$value;
    }

}