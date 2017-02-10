<?php

namespace plugins\FormValidationEngine\Form;


/**
 * Form Validation Engine
 * 
 * Questa classe è adattata da quella nativa di CodeIgniter, molto versatile e leggera, utile ed efficente al punto giusto!
 * 
 * @see http://codeigniter.com/user_guide/libraries/form_validation.html
 */
class FormValidationEngine extends FormValidationSecurity
{

    /**
     * Metodo del form POST
     * @var String
     */
    const FORM_METHOD_POST = 'POST';
    
    /**
     * Metodo del form GET
     * @var String
     */
    const FORM_METHOD_GET  = 'GET';
    
    /**
     * Callback context for validation "callback_"
     * 
     * @var object
     */
    protected $callback_context = null;

    /**
     * Validation data for the current form submission
     *
     * @var array
     */
    protected $_field_data = array();

    /**
     * Validation rules for the current form
     *
     * @var array
     */
    protected $_config_rules = array();

    /**
     * Array of validation errors
     *
     * @var array
     */
    protected $_error_array = array();

    /**
     * Array of custom error messages
     *
     * @var array
     */
    protected $_error_messages = array();

    /**
     * Start tag for error wrapping
     *
     * @var string
     */
    protected $_error_prefix = '<p>';

    /**
     * End tag for error wrapping
     *
     * @var string
     */
    protected $_error_suffix = '</p>';

    /**
     * Custom error message
     *
     * @var string
     */
    protected $error_string = '';

    /**
     * Whether the form data has been validated as safe
     *
     * @var bool
     */
    protected $_safe_form_data = false;

    /**
     * Custom data to validate
     *
     * @var array
     */
    protected $validation_data = array();
    
    /**
     * HttpRequest
     * 
     * @var \Application_HttpRequest
     */
    protected $_httpRequest = null;
    
    /**
     * Prefisso per le strihge di validazione
     * @var String
     */
    protected $_validation_messages_prefix   = 'FORM_VALIDATION_';
    
    /**
     * Domini nel quale ricercare le stringhe di validazione in base al nome della rule
     * @var Array
     */
    protected $_validation_messages_domain = array('validation','form');
    
    /**
     * List of never allowed strings
     *
     * @var	array
     */
    protected $_never_allowed_str =	array(
            'document.cookie'	=> '[removed]',
            'document.write'	=> '[removed]',
            '.parentNode'       => '[removed]',
            '.innerHTML'	=> '[removed]',
            '-moz-binding'	=> '[removed]',
            '<!--'		=> '&lt;!--',
            '-->'		=> '--&gt;',
            '<![CDATA['		=> '&lt;![CDATA[',
            '<comment>'		=> '&lt;comment&gt;'
    );
    
    /**
     * Classe che contiene le rules di validazione
     * 
     * @var FormValidationRules
     */
    protected $_validationRules;
        
    /**
     * Inizializza il form engine
     * 
     * @param   array $rules
     */
    public function __construct($rules = array(),array $configs = array())
    {
        // applies delimiters set in config file.
        if (isset($rules['error_prefix']))
        {
            $this->_error_prefix = $rules['error_prefix'];
            unset($rules['error_prefix']);
        }
        if (isset($rules['error_suffix']))
        {
            $this->_error_suffix = $rules['error_suffix'];
            unset($rules['error_suffix']);
        }

        $this->_httpRequest  = $this->getApplicationKernel()->getApplicationHttpRequest();
        $this->_formMethod   = self::FORM_METHOD_POST;
        $this->_config_rules = $rules;
        
        foreach($this->_validation_messages_domain as $domain)
        {
            $this->load_translations_messages($domain);
        }
        
        $this->_validationRules = new FormValidationRules();
        
        parent::__construct($configs);
    }

    // --------------------------------------------------------------------
    
    /**
     * Carica le stringhe di traduzione per un determinato dominio e locale
     * 
     * @param string $domain    dominio
     * @param string $locale    [OPZIONALE] locale, default locale application
     * 
     * @return FormValidationEngine
     */
    public function load_translations_messages($domain,$locale = null)
    {
        $appLanguage  = $this->getApplicationKernel()->getApplicationLanguages();
        $translations = $appLanguage->getLanguageCatalogueDataCurrent()->getAllTranslations($domain);
                
        if(!$translations)
        {
            $translations = array();
        }
        
        $this->_error_messages  = array_merge($this->_error_messages,$translations);
                
        return $this;
    }
    
    
    /**
     * Restituisce la classe che contiene le regole di validazione
     * 
     * @return FormValidationRules
     */
    public function get_validaion_rules()
    {
        return $this->_validationRules;
    }
    
    /**
     * Imposta lo scope corrente della classe
     * 
     * @param Mixed $currentScope  object
     * 
     * @return \Form_FormValidationEngine
     */
    public function set_callback_context($callback_context)
    {
        if($callback_context && is_object($callback_context))
        {
            $this->callback_context = $callback_context;
            return $this;
        }
        
        return self::throwNewException(45938475981044, 'FormValidation: $callback_context non è un oggeto valido sul quale ricercare i metodi callbacl_*');
    }

    /**
     * Imposta i dati per la validazione
     * 
     * @param Array $validation_data Dati
     * 
     * @return \Form_FormValidationEngine
     */
    public function set_validation_data($validation_data)
    {
        $this->validation_data = $validation_data;
        return $this;
    }
    
    /**
     * Imposta le rules
     *
     * Questa funzione accetta un array dei nomi di campi e le loro validazioni, con eventuali label e messaggi di errori custom, storandoli nell'oggetto
     * 
     * @param	mixed	$field      Array dei campi
     * @param	string	$label      Labels
     * @param	mixed	$rules      Rules
     * @param	mixed	$errors     Errors messages, single message for all validation 
     * 
     * @return	Form_FormValidationEngine
     */
    public function set_rules($field, $label = '', $rules = array(), $errors = array())
    {
        /**
         * Se non ho dati in POST
         */
        if ($this->_formMethod != $this->_httpRequest->getMethod()  && empty($this->validation_data))
        {
            return $this;
        }

        // If an array was passed via the first parameter instead of individual string
        // values we cycle through it and recursively call this function.
        if (is_array($field))
        {
            foreach ($field as $row)
            {
                // Houston, we have a problem...
                if (!isset($row['field'], $row['rules']))
                {
                    continue;
                }

                // If the field label wasn't passed we use the field name
                $label = isset($row['label']) ? $row['label'] : $row['field'];

                // Add the custom error message array
                $errors = (isset($row['errors']) && is_array($row['errors'])) ? $row['errors'] : array();

                // Here we go!
                $this->set_rules($row['field'], $label, $row['rules'], $errors);
            }

            return $this;
        }

        // No fields? Nothing to do...
        if (!is_string($field) OR $field === '')
        {
            return $this;
        }
        elseif (!is_array($rules))
        {
            // BC: Convert pipe-separated rules string to an array
            if (is_string($rules))
            {
                $rules = explode('|', $rules);
            }
            else
            {
                return $this;
            }
        }

        // If the field label wasn't passed we use the field name
        $label = ($label === '') ? $field : $label;

        $indexes = array();

        // Is the field name an array? If it is an array, we break it apart
        // into its components so that we can fetch the corresponding POST data later
        if (($is_array = (bool) preg_match_all('/\[(.*?)\]/', $field, $matches)) === true)
        {
            sscanf($field, '%[^[][', $indexes[0]);

            for ($i = 0, $c = count($matches[0]); $i < $c; $i++)
            {
                if ($matches[1][$i] !== '')
                {
                    $indexes[] = $matches[1][$i];
                }
            }
        }
        
        /**
         * Set same error for all rules
         */
        if(is_string($errors))
        {
            $errors_string = $errors;
            $errors        = array();
            
            foreach($rules as $rule)
            {
                $errors[$rule] = $errors_string;
            }
        }
        
        // Build our master array
        $this->_field_data[$field] = array(
            'field' => $field,
            'label' => $label,
            'rules' => $rules,
            'errors' => $errors,
            'is_array' => $is_array,
            'keys' => $indexes,
            'postdata' => null,
            'error' => ''
        );
        

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * By default, form validation uses the $_POST array to validate
     *
     * If an array is set through this method, then this array will
     * be used instead of the $_POST array
     *
     * Note that if you are validating multiple arrays, then the
     * reset_validation() function should be called after validating
     * each array due to the limitations of CI's singleton
     *
     * @param	array	$data
     * @return	Form_FormValidationEngine
     */
    public function set_data(array $data)
    {
        if (!empty($data))
        {
            $this->validation_data = $data;
        }

        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Set Error Message
     *
     * Lets users set their own error messages on the fly. Note:
     * The key name has to match the function name that it corresponds to.
     *
     * @param	array
     * @param	string
     * @return	Form_FormValidationEngine
     */
    public function set_message($lang, $val = '')
    {
        if (!is_array($lang))
        {
            $lang = array($lang => $val);
        }

        $this->_error_messages = array_merge($this->_error_messages, $lang);
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Set The Error Delimiter
     *
     * Permits a prefix/suffix to be added to each error message
     *
     * @param	string
     * @param	string
     * @return	Form_FormValidationEngine
     */
    public function set_error_delimiters($prefix = '<p>', $suffix = '</p>')
    {
        $this->_error_prefix = $prefix;
        $this->_error_suffix = $suffix;
        return $this;
    }

    // --------------------------------------------------------------------

    /**
     * Get Error Message
     *
     * Gets the error message associated with a particular field
     *
     * @param	string	$field	Field name
     * @param	string	$prefix	HTML start tag
     * @param 	string	$suffix	HTML end tag
     * @return	string
     */
    public function error($field, $prefix = '', $suffix = '')
    {
        if (empty($this->_field_data[$field]['error']))
        {
            return '';
        }

        if ($prefix === '')
        {
            $prefix = $this->_error_prefix;
        }

        if ($suffix === '')
        {
            $suffix = $this->_error_suffix;
        }

        return $prefix . $this->_field_data[$field]['error'] . $suffix;
    }

    /**
     * Return error of field
     * 
     * @param String $field         Field Name
     * 
     * @return String
     */
    public function error_field($field)
    {
        if(isset($this->_field_data[$field]) && isset($this->_field_data[$field]["error"]))
        {
            return $this->_field_data[$field]["error"];
        }
        
        return "";
    }
    
    // --------------------------------------------------------------------

    /**
     * Get Array of Error Messages
     *
     * Returns the error messages as an array
     *
     * @return	array
     */
    public function error_array()
    {
        return $this->_error_array;
    }

    // --------------------------------------------------------------------

    /**
     * Error String
     *
     * Returns the error messages as a string, wrapped in the error delimiters
     *
     * @param	string
     * @param	string
     * @return	string
     */
    public function error_string($prefix = '', $suffix = '')
    {
        // No errors, validation passes!
        if (count($this->_error_array) === 0)
        {
            return '';
        }

        if ($prefix === '')
        {
            $prefix = $this->_error_prefix;
        }

        if ($suffix === '')
        {
            $suffix = $this->_error_suffix;
        }

        // Generate the error string
        $str = '';
        foreach ($this->_error_array as $val)
        {
            if ($val !== '')
            {
                $str .= $prefix . $val . $suffix . "\n";
            }
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Run the Validator
     *
     * This function does all the work.
     *
     * @param	string	$group
     * @return	bool
     */
    public function run($group = '')
    {
        // Do we even have any data to process?  Mm?
        $validation_array = empty($this->validation_data) ? $this->_httpRequest->getPost()->getAll() : $this->validation_data;
        if (count($validation_array) === 0)
        {
            return false;
        }

        if(isset($this->_configs['CSRF_PROTECTION'])  && $this->_configs['CSRF_PROTECTION'])
        {
           $this->csrf_verify();
        }
                
        // Does the _field_data array containing the validation rules exist?
        // If not, we look to see if they were assigned via a config file
        if (count($this->_field_data) === 0)
        {
            // No validation rules?  We're done...
            if (count($this->_config_rules) === 0)
            {
                return false;
            }            

            $this->set_rules(isset($this->_config_rules[$group]) ? $this->_config_rules[$group] : $this->_config_rules);

            // Were we able to set the rules correctly?
            if (count($this->_field_data) === 0)
            {
                return false;
            }
        }        
        
        // Cycle through the rules for each field and match the corresponding $validation_data item
        foreach ($this->_field_data as $field => $row)
        {
            // Fetch the data from the validation_data array item and cache it in the _field_data array.
            // Depending on whether the field name is an array or a string will determine where we get it from.
            if ($row['is_array'] === true)
            {
                $this->_field_data[$field]['postdata'] = $this->_reduce_array($validation_array, $row['keys']);
            }
            elseif (isset($validation_array[$field]) && $validation_array[$field] !== '')
            {
                $this->_field_data[$field]['postdata'] = $validation_array[$field];
            }
        }
        
        $this->_validationRules->set_field_data($this->_field_data);

        // Execute validation rules
        // Note: A second foreach (for now) is required in order to avoid false-positives
        //	 for rules like 'matches', which correlate to other validation fields.
        foreach ($this->_field_data as $field => $row)
        {
            // Don't try to validate if we have no rules set
            if (empty($row['rules']))
            {
                continue;
            }

            $this->_execute($row, $row['rules'], $this->_field_data[$field]['postdata']);
        }

        // Did we end up with any errors?
        $total_errors = count($this->_error_array);
        if ($total_errors > 0)
        {
            $this->_safe_form_data = true;
        }

        // Now we need to re-set the POST data with the new, processed data
        $this->_reset_post_array();

        return ($total_errors === 0);
    }

    // --------------------------------------------------------------------

    /**
     * Traverse a multidimensional $_POST array index until the data is found
     *
     * @param	array
     * @param	array
     * @param	int
     * @return	mixed
     */
    protected function _reduce_array($array, $keys, $i = 0)
    {
        if (is_array($array) && isset($keys[$i]))
        {
            return isset($array[$keys[$i]]) ? $this->_reduce_array($array[$keys[$i]], $keys, ($i + 1)) : null;
        }

        // null must be returned for empty fields
        return ($array === '') ? null : $array;
    }

    // --------------------------------------------------------------------

    /**
     * Re-populate the _POST array with our finalized and processed data
     *
     * @return	void
     */
    protected function _reset_post_array()
    {
        $postData = $this->_httpRequest->getPost();

        foreach ($this->_field_data as $field => $row)
        {
            if ($row['postdata'] !== null)
            {
                if ($row['is_array'] === false)
                {
                    if ($postData->offsetExists($row['field']))
                    {
                        $postData->addIndex($row['field'],$row['postdata']);
                    }
                }
                else
                {
                    // start with a reference
                    $post_ref =  $postData->getAll();

                    // before we assign values, make a reference to the right POST key
                    if (count($row['keys']) === 1)
                    {
                        $post_ref = & $post_ref[current($row['keys'])];
                    }
                    else
                    {
                        foreach ($row['keys'] as $val)
                        {
                            $post_ref = & $post_ref[$val];
                        }
                    }

                    if (is_array($row['postdata']))
                    {
                        $array = array();
                        foreach ($row['postdata'] as $k => $v)
                        {
                            $array[$k] = $v;
                        }

                        $post_ref = $array;
                    }
                    else
                    {
                        $post_ref = $row['postdata'];
                    }
                }
            }
        }
        
        $this->_httpRequest->getPost()->exchangeArray($postData);
    }

    // --------------------------------------------------------------------

    /**
     * Executes the Validation routines
     *
     * @param	array
     * @param	array
     * @param	mixed
     * @param	int
     * @return	mixed
     */
    protected function _execute($row, $rules, $postdata = null, $cycles = 0)
    {
        // If the $_POST data is an array we will run a recursive call
        if (is_array($postdata))
        {
            foreach ($postdata as $key => $val)
            {
                $this->_execute($row, $rules, $val, $key);
            }

            return;
        }
        
        if(!$this->callback_context && $this->getApplicationKernel()->has('@controller.action'))
        {
            $this->callback_context = $this->getApplicationKernel()->get('@controller.action');
        }

        // If the field is blank, but NOT required, no further tests are necessary
        $callback = false;
        if (!in_array('required', $rules) && ($postdata === null OR $postdata === ''))
        {
            // Before we bail out, does the rule contain a callback?
            foreach ($rules as &$rule)
            {
                if (is_string($rule))
                {
                    if (strncmp($rule, 'callback_', 9) === 0)
                    {
                        $callback = true;
                        $rules = array(1 => $rule);
                        break;
                    }
                }
                elseif (is_callable($rule))
                {
                    $callback = true;
                    $rules = array(1 => $rule);
                    break;
                }
            }

            if (!$callback)
            {
                return;
            }
        }

        // Isset Test. Typically this rule will only apply to checkboxes.
        if (($postdata === null OR $postdata === '') && !$callback)
        {
            if (in_array('isset', $rules, true) OR in_array('required', $rules))
            {
                // Set the message type
                $type = in_array('required', $rules) ? 'required' : 'isset';
                
                $langType     = $this->_validation_messages_prefix.$type;
                $langKeyUpper = strtoupper($langType);
                $langKeyLower = strtoupper($langType);
                
                // Check if a custom message is defined
                if (isset($this->_field_data[$row['field']]['errors'][$type]))
                {
                    $line = $this->_field_data[$row['field']]['errors'][$type];
                }
                else if (isset($this->_error_messages[$langType]))
                {
                    $line = $this->_error_messages[$langType];
                }
                else if (isset($this->_error_messages[$langKeyUpper]))
                {
                    $line = $this->_error_messages[$langKeyUpper];
                }
                else if (isset($this->_error_messages[$langKeyLower]))
                {
                    $line = $this->_error_messages[$langKeyLower];
                }
                else if (isset($this->_error_messages[$type]))
                {
                    $line = $this->_error_messages[$type];
                }

                // Build the error message
                $message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']));

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if (!isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }
            }

            return;
        }

        // --------------------------------------------------------------------
        // Cycle through each rule and run it
        foreach ($rules as $rule)
        {
            $_in_array = false;

            // We set the $postdata variable with the current data in our master array so that
            // each cycle of the loop is dealing with the processed data from the last cycle
            if ($row['is_array'] === true && is_array($this->_field_data[$row['field']]['postdata']))
            {
                // We shouldn't need this safety, but just in case there isn't an array index
                // associated with this cycle we'll bail out
                if (!isset($this->_field_data[$row['field']]['postdata'][$cycles]))
                {
                    continue;
                }

                $postdata = $this->_field_data[$row['field']]['postdata'][$cycles];
                $_in_array = true;
            }
            else
            {
                // If we get an array field, but it's not expected - then it is most likely
                // somebody messing with the form on the client side, so we'll just consider
                // it an empty field
                $postdata = is_array($this->_field_data[$row['field']]['postdata']) ? null : $this->_field_data[$row['field']]['postdata'];
            }
            
            // Is the rule a callback?
            $callback = $callable = false;
            
            
            // Strip the parameter (if exists) from the rule
            // Rules can contain a parameter: max_length[5]
            $param = false;
            if (!$callable && preg_match('/(.*?)\[(.*)\]/', $rule, $match))
            {
                $rule = $match[1];
                $param = $match[2];
            }
            
            if (is_string($rule))
            {
                if (strpos($rule, 'callback_') === 0)
                {
                    $rule     = substr($rule, 9);
                    $callback = true;
                }
               
                if($this->callback_context && method_exists($this->callback_context,$rule))
                {
                    $callable = $this->callback_context;
                }
            }
            elseif (is_callable($rule) || (is_array($rule) && isset($rule[0], $rule[1]) && is_callable($rule[1])))
            {
                // We have a "named" callable, so save the name
                $callable = $rule[0];
                $rule     = $rule[1];
            }


            // Call the function that corresponds to the rule
            if ($callback OR $callable !== false)
            {
                if($callable)
                {
                    $result = call_user_func_array(array($callable,$rule), array($postdata,$param));
                }
                else 
                {
                    $result = is_array($rule) ? $rule[0]->{$rule[1]}($postdata) : $rule($postdata);

                    // Is $callable set to a rule name?
                    if ($callable !== false)
                    {
                        $rule = $callable;
                    }
                }

                // Re-assign the result to the master data array
                if ($_in_array === true)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
                }

                // If the field isn't required and we just processed a callback we'll move on...
                if (!in_array('required', $rules, true) && $result !== false)
                {
                    continue;
                }
            }
            else
            {
                $result = $this->_validationRules->$rule($postdata, $param);

                if ($_in_array === true)
                {
                    $this->_field_data[$row['field']]['postdata'][$cycles] = is_bool($result) ? $postdata : $result;
                }
                else
                {
                    $this->_field_data[$row['field']]['postdata'] = is_bool($result) ? $postdata : $result;
                }
            }

            // Did the rule test negatively? If so, grab the error.
            if ($result === false)
            {
                // Callable rules might not have named error messages
                if (!is_string($rule))
                {
                    return;
                }
                
                $langRule     = $this->_validation_messages_prefix.$rule;
                $langRuleUpper = strtoupper($langRule);
                $langRuleLower = strtoupper($langRule);
                
                if (isset($this->_error_array[$row['field']]))
                {
                    $message = str_replace('%s',!empty($row['label']) ? $row['label'] : $row['field'],$this->_error_messages[$row['field']]);
                    $this->_error_array[$row['field']]         = $message;
                    $this->_field_data[$row['field']]['error'] = $message;
                }
                else if (isset($this->_field_data[$row['field']]['errors'][$rule]))
                {
                    $line = $this->_field_data[$row['field']]['errors'][$rule];
                }
                // Check if a custom message is defined
                else if (isset($this->_error_messages[$langRule]))
                {
                    $line = $this->_error_messages[$langRule];
                }
                else if (isset($this->_error_messages[$langRuleUpper]))
                {
                    $line = $this->_error_messages[$langRuleUpper];
                }
                else if (isset($this->_error_messages[$langRuleLower]))
                {
                    $line = $this->_error_messages[$langRuleLower];
                }
                elseif (!isset($this->_error_messages[$rule]))
                {
                    $line = 'Unable to access an error message corresponding to your field name for rule: '.$rule;
                }           
                else
                {
                    $line = $this->_error_messages[$rule];
                }

                // Is the parameter we are inserting into the error message the name
                // of another field? If so we need to grab its "field label"
                if (isset($this->_field_data[$param], $this->_field_data[$param]['label']))
                {
                    $param = $this->_translate_fieldname($this->_field_data[$param]['label']);
                }

                // Build the error message
                $message = $this->_build_error_msg($line, $this->_translate_fieldname($row['label']), $param);

                // Save the error message
                $this->_field_data[$row['field']]['error'] = $message;

                if (!isset($this->_error_array[$row['field']]))
                {
                    $this->_error_array[$row['field']] = $message;
                }

                return;
            }
        }
    }

    // --------------------------------------------------------------------

    /**
     * Translate a field name
     *
     * @param	string	the field name
     * @return	string
     */
    protected function _translate_fieldname($fieldname)
    {
        // Do we need to translate the field name?
        // We look for the prefix lang: to determine this
//        if (sscanf($fieldname, 'lang:%s', $line) === 1)
//        {
//            // Were we able to translate the field name?  If not we use $line
//            if (false === ($fieldname = $this->CI->lang->line('form_validation_' . $line))
//                    // DEPRECATED support for non-prefixed keys
//                    && false === ($fieldname = $this->CI->lang->line($line, false)))
//            {
//                return $line;
//            }
//        }

        return $fieldname;
    }

    // --------------------------------------------------------------------

    /**
     * Build an error message using the field and param.
     *
     * @param	string	The error message line
     * @param	string	A field's human name
     * @param	mixed	A rule's optional parameter
     * @return	string
     */
    protected function _build_error_msg($line, $field = '', $param = '')
    {
        // Check for %s in the string for legacy support.
        if (strpos($line, '%s') !== false)
        {
            return sprintf($line, $field, $param);
        }

        return str_replace(array('{field}', '{param}'), array($field, $param), $line);
    }

    // --------------------------------------------------------------------

    /**
     * Checks if the rule is present within the validator
     *
     * Permits you to check if a rule is present within the validator
     *
     * @param	string	the field name
     * @return	bool
     */
    public function has_rule($field)
    {
        return isset($this->_field_data[$field]);
    }

    // --------------------------------------------------------------------

    /**
     * Get the value from a form
     *
     * Permits you to repopulate a form field with the value it was submitted
     * with, or, if that value doesn't exist, with the default
     *
     * @param	string	the field name
     * @param	string
     * @return	string
     */
    public function set_value($field = '', $default = '')
    {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata']))
        {
            return $default;
        }

        // If the data is an array output them one at a time.
        //	E.g: form_input('name[]', set_value('name[]');
        if (is_array($this->_field_data[$field]['postdata']))
        {
            return array_shift($this->_field_data[$field]['postdata']);
        }

        return $this->_field_data[$field]['postdata'];
    }

    // --------------------------------------------------------------------

    /**
     * Set Select
     *
     * Enables pull-down lists to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     * @param	bool
     * @return	string
     */
    public function set_select($field = '', $value = '', $default = false)
    {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata']))
        {
            return ($default === true && count($this->_field_data) === 0) ? ' selected="selected"' : '';
        }

        $field = $this->_field_data[$field]['postdata'];
        $value = (string) $value;
        if (is_array($field))
        {
            // Note: in_array('', array(0)) returns true, do not use it
            foreach ($field as &$v)
            {
                if ($value === $v)
                {
                    return ' selected="selected"';
                }
            }

            return '';
        }
        elseif (($field === '' OR $value === '') OR ( $field !== $value))
        {
            return '';
        }

        return ' selected="selected"';
    }

    // --------------------------------------------------------------------

    /**
     * Set Radio
     *
     * Enables radio buttons to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     * @param	bool
     * @return	string
     */
    public function set_radio($field = '', $value = '', $default = false)
    {
        if (!isset($this->_field_data[$field], $this->_field_data[$field]['postdata']))
        {
            return ($default === true && count($this->_field_data) === 0) ? ' checked="checked"' : '';
        }

        $field = $this->_field_data[$field]['postdata'];
        $value = (string) $value;
        if (is_array($field))
        {
            // Note: in_array('', array(0)) returns true, do not use it
            foreach ($field as &$v)
            {
                if ($value === $v)
                {
                    return ' checked="checked"';
                }
            }

            return '';
        }
        elseif (($field === '' OR $value === '') OR ( $field !== $value))
        {
            return '';
        }

        return ' checked="checked"';
    }

    // --------------------------------------------------------------------

    /**
     * Set Checkbox
     *
     * Enables checkboxes to be set to the value the user
     * selected in the event of an error
     *
     * @param	string
     * @param	string
     * @param	bool
     * @return	string
     */
    public function set_checkbox($field = '', $value = '', $default = false)
    {
        // Logic is exactly the same as for radio fields
        return $this->set_radio($field, $value, $default);
    }

    /**
     * Reset validation vars
     *
     * Prevents subsequent validation routines from being affected by the
     * results of any previous validation routine due to the CI singleton.
     *
     * @return	Form_FormValidationEngine
     */
    public function reset_validation()
    {
        $this->_field_data = array();
        $this->_config_rules = array();
        $this->_error_array = array();
        $this->_error_messages = array();
        $this->error_string = '';
        
        return $this;
    }

    
   /**
    * Force an error message for the specified field
    * 
    * @access public
    * 
    * @param string	$field   field
    * @param string	$type    the error type to be displayed (for obtaining the preset message)
    * 
    * @return FormValidationEngine
    */
    public function manual_set_error($field, $type)
    {
        $this->_error_array[$field]         = $this->_error_messages[$type];
        $this->_field_data[$field]['error'] = $this->_error_messages[$type];
        
        return $this;
    }

   /**
    * Add field error message and set as validation failed for field
    * 
    * @access public
    * @param string	$field  
    * @param string	$message
    */
    public function manual_set_error_message($field, $message)
    {
        $this->_error_array[$field]         = $message;
        $this->_field_data[$field]['error'] = $message;
        $this->_error_messages[$field]      = $message;
        
        return $this;
    }
    
    
    /**
     * Retur all forms errors
     * 
     * @return array
     */
    public function get_errors()
    {
        return $this->_error_array;
    }
}
