<?php

if ( ! function_exists('form_open'))
{
    /**
     * Form Declaration
     *
     * Crea il tag di apertura del form
     *
     * @param String  $route      Rotta/Url default NULL
     * @param Array   $attributes Coppia attributo/valore html
     * @param Array   $hidden     Coppia nome/valore campi hidden
     * 
     * @return	String
     */
    function form_open($route = null, $attributes = array(), $hidden = array())
    {
        $kernel     = getApplicationKernel();

        // If no action is provided then set to the current url
        $action     = !$route ? $kernel->routing->getCurrentUrl() :  $kernel->routing->generateUrl($route);
       
        $attributes = _attributes_to_string($attributes);

        if (stripos($attributes, 'method=') === FALSE)
        {
                $attributes .= ' method="POST"';
        }

        if (stripos($attributes, 'accept-charset=') === FALSE)
        {
                $attributes .= ' accept-charset="'.strtolower($kernel->config->getConfigsValue('SITE_CHARSET')).'"';
        }

        $form = '<form action="'.$action.'"'.$attributes.">\n";

        // Add CSRF field if enabled, but leave it out for GET requests and requests to external websites
        if ($kernel->config->getConfigsValue('FORM_CSRF_PROTECTION') && ! stripos($form, 'method="get"'))
        {
           $hidden[$kernel->form_validation->get_csrf_token_name()] = $kernel->form_validation->get_csrf_hash();
        }

        if (is_array($hidden))
        {
            foreach ($hidden as $name => $value)
            {
               $form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value).'" style="display:none;" />'."\n";
            }
        }

        return $form;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_open_multipart'))
{
    /**
     * 
     * Apre il form in "multipart/form-data".
     *
     * @param String  $route      Rotta/Url default NULL
     * @param Array   $attributes Coppia attributo/valore html
     * @param Array   $hidden     Coppia nome/valore campi hidden
     * 
     * @return	string
     */
    function form_open_multipart($route = NULL, $attributes = array(), $hidden = array())
    {
        if (is_string($attributes))
        {
                $attributes .= ' enctype="multipart/form-data"';
        }
        else
        {
                $attributes['enctype'] = 'multipart/form-data';
        }

        return form_open($route, $attributes, $hidden);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_hidden'))
{
    /**
     * Hidden Input Field
     *
     * Genera campi nascosti. È possibile passare una chiave semplice stringa / valore o
     * Un array associativo con più valori .
     *
     * @param   Mixed   $name        Nome campo
     * @param   String  $value	 Nome campo
     * @param   Bool    $recursing
     * 
     * @return  String
     */
    function form_hidden($name, $value = '', $recursing = FALSE)
    {
        static $form;

        if ($recursing === FALSE)
        {
            $form = "\n";
        }

        if (is_array($name))
        {
            foreach ($name as $key => $val)
            {
                form_hidden($key, $val, TRUE);
            }

            return $form;
        }

        if ( ! is_array($value))
        {
            $form .= '<input type="hidden" name="'.$name.'" value="'.form_prep($value)."\" />\n";
        }
        else
        {
            foreach ($value as $k => $v)
            {
                $k = is_int($k) ? '' : $k;
                form_hidden($name.'['.$k.']', $v, TRUE);
            }
        }

        return $form;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_input'))
{
    /**
     * Genera un campo di testo
     *
     * @param  Mixed   $data nome del campo/ attributi, default ''
     * @param  String  $value valore, default ''
     * @param  String  $extra stringa extra da appendere al campo, default ''
     * 
     * @return String
     */
    function form_input($data = '', $value = '', $extra = '')
    {
        $defaults = array(
                'type' => 'text',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults).$extra." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_password'))
{
    /**
     * Password Field
     *
     * Genera un campo "password"
     *
     * @param  Mixed   $data nome del campo/ attributi, default ''
     * @param  String  $value valore, default ''
     * @param  String  $extra stringa extra da appendere al campo, default ''
     * 
     * @return String
     */
    function form_password($data = '', $value = '', $extra = '')
    {
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'password';
        return form_input($data, $value, $extra);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_upload'))
{
    /**
     * Upload Field
     * 
     * Genera un campo file
     *
     * @param  Mixed   $data nome del campo/ attributi, default ''
     * @param  String  $value valore, default ''
     * @param  String  $extra stringa extra da appendere al campo, default ''
     * 
     * @return String
     */
    function form_upload($data = '', $value = '', $extra = '')
    {
        $defaults = array('type' => 'file', 'name' => '');
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'file';
        return '<input '._parse_form_attributes($data, $defaults).$extra." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_textarea'))
{
    /**
     * Textarea field
     *
     * @param  Mixed   $data nome del campo/ attributi, default ''
     * @param  String  $value valore, default ''
     * @param  String  $extra stringa extra da appendere al campo, default ''
     * 
     * @return	string
     */
    function form_textarea($data = '', $value = '', $extra = '')
    {
        $defaults = array(
                'name' => is_array($data) ? '' : $data,
                'cols' => '40',
                'rows' => '10'
        );

        if ( ! is_array($data) OR ! isset($data['value']))
        {
            $val = $value;
        }
        else
        {
            $val = $data['value'];
            unset($data['value']); // textareas don't use the value attribute
        }

        return '<textarea '._parse_form_attributes($data, $defaults).$extra.'>'.form_prep($val, TRUE)."</textarea>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_multiselect'))
{
    /**
     * Multi-select menu
     * 
     * Genera una select multiple
     *
     * @param   String $name     Nome
     * @param   Array  $options  Array opzioni
     * @param   Mixed  $selected Array selezionati
     * @param   String $extra    Stringa aggiunga a fine campo
     * 
     * @return  String
     */
    function form_multiselect($name = '', $options = array(), $selected = array(), $extra = '')
    {
        if ( ! strpos($extra, 'multiple'))
        {
            $extra .= ' multiple="multiple"';
        }

        return form_dropdown($name, $options, $selected, $extra);
    }
}

// --------------------------------------------------------------------

if ( ! function_exists('form_dropdown'))
{
    /**
     * Drop-down Menu
     * 
     * Genera una select
     *
     * @param  String $name     Nome
     * @param  Array  $options  Array opzioni
     * @param  Mixed  $selected Array selezionati
     * @param  String $extra    Stringa aggiunga a fine campo
     * 
     * @return String
     */
    function form_dropdown($data = '', $options = array(), $selected = array(), $extra = '')
    {
        $defaults = array();
        
        $kernel   = getApplicationKernel();
        
        $post     = $kernel->httprequest->getPost()->getAll();

        if (is_array($data))
        {
            if (isset($data['selected']))
            {
                $selected = $data['selected'];
                unset($data['selected']); // select tags don't have a selected attribute
            }

            if (isset($data['options']))
            {
                $options = $data['options'];
                unset($data['options']); // select tags don't use an options attribute
            }
        }
        else
        {
            $defaults = array('name' => $data);
        }

        is_array($selected) OR $selected = array($selected);
        is_array($options) OR $options = array($options);

        // If no selected state was submitted we will attempt to set it automatically
        if (empty($selected))
        {
            if (is_array($data))
            {
                if (isset($data['name'], $post[$data['name']]))
                {
                   $selected = array($post[$data['name']]);
                }
            }
            elseif (isset($post[$data]))
            {
                $selected = array($post[$data]);
            }
        }

        $extra = _attributes_to_string($extra);

        $multiple = (count($selected) > 1 && strpos($extra, 'multiple') === FALSE) ? ' multiple="multiple"' : '';

        $form = '<select '.rtrim(_parse_form_attributes($data, $defaults)).$extra.$multiple.">\n";

        foreach ($options as $key => $val)
        {
            $key = (string) $key;

            if (is_array($val))
            {
                if (empty($val))
                {
                        continue;
                }

                $form .= '<optgroup label="'.$key."\">\n";

                foreach ($val as $optgroup_key => $optgroup_val)
                {
                    $sel = in_array($optgroup_key, $selected) ? ' selected="selected"' : '';
                    $form .= '<option value="'.form_prep($optgroup_key).'"'.$sel.'>'
                            .(string) $optgroup_val."</option>\n";
                }

                $form .= "</optgroup>\n";
            }
            else
            {
                $form .= '<option value="'.form_prep($key).'"'
                        .(in_array($key, $selected) ? ' selected="selected"' : '').'>'
                        .(string) $val."</option>\n";
            }
        }

        return $form."</select>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_checkbox'))
{
    /**
     * Checkbox Field
     * 
     * Genera un campo checkbox
     *
     * @param  Mixed   $data Nome del campo/attributi, default ''
     * @param  String  $value Valore, default ''
     * @param  Bool    $checked Indica se checked, default FALSE
     * @param  String  $extra Stringa extra aggiuta a fine campo
     * 
     * @return  String
     */
    function form_checkbox($data = '', $value = '', $checked = FALSE, $extra = '')
    {
        $defaults = array('type' => 'checkbox', 'name' => ( ! is_array($data) ? $data : ''), 'value' => $value);

        if (is_array($data) && array_key_exists('checked', $data))
        {
            $checked = $data['checked'];

            if ($checked == FALSE)
            {
                unset($data['checked']);
            }
            else
            {
                $data['checked'] = 'checked';
            }
        }

        if ($checked == TRUE)
        {
            $defaults['checked'] = 'checked';
        }
        else
        {
            unset($defaults['checked']);
        }
        
        return '<input '._parse_form_attributes($data, $defaults).$extra." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_radio'))
{
    /**
     * Radio Button
     * 
     * Genera un radio button
     *
     * @param Mixed  $data    Nome del campo/attributi, default ''
     * @param String $value   Valore, default ''
     * @param Bool   $checked Indica se checked, default FALSE
     * @param String $extra   Stringa extra aggiuta a fine campo
     * 
     * @return  String
     */
    function form_radio($data = '', $value = '', $checked = FALSE, $extra = '')
    {
        is_array($data) OR $data = array('name' => $data);
        $data['type'] = 'radio';
        return form_checkbox($data, $value, $checked, $extra);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_submit'))
{
    /**
     * Submit Button
     * 
     * Genera un submit button
     *
     * @param Mixed   $data  Nome del campo/attributi, default ''
     * @param String  $value valore, default ''
     * @param String  $extra Stringa extra aggiuta a fine campo
     * 
     * @return  String
     */
    function form_submit($data = '', $value = '', $extra = '')
    {
        $defaults = array(
                'type' => 'submit',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults).$extra." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_reset'))
{
    /**
     * Reset Button
     * 
     * Genera un button reset
     *
     * @param Mixed   $data  Nome del campo/attributi, default ''
     * @param String  $value valore, default ''
     * @param String  $extra Stringa extra aggiuta a fine campo
     * 
     * @return  String
     */
    function form_reset($data = '', $value = '', $extra = '')
    {
        $defaults = array(
                'type' => 'reset',
                'name' => is_array($data) ? '' : $data,
                'value' => $value
        );

        return '<input '._parse_form_attributes($data, $defaults).$extra." />\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_button'))
{
    /**
     * Form Button
     *
     * Genera un button
     *
     * @param Mixed  $data    Nome del campo/attributi, default ''
     * @param String $content Contenuto button, default ''
     * @param String $extra   Stringa extra aggiuta a fine campo
     * 
     * @return  String
     */
    function form_button($data = '', $content = '', $extra = '')
    {
        $defaults = array(
                'name' => is_array($data) ? '' : $data,
                'type' => 'button'
        );

        if (is_array($data) && isset($data['content']))
        {
                $content = $data['content'];
                unset($data['content']); // content is not an attribute
        }

        return '<button '._parse_form_attributes($data, $defaults).$extra.'>'.$content."</button>\n";
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_label'))
{
    /**
     * Form Label Tag
     * 
     * Genera una label
     *
     * @param String $label_text  Testo
     * @param String $id          Id campo
     * @param Array  $attributes  Attributi
     * 
     * @return String
     */
    function form_label($label_text = '', $id = '', $attributes = array())
    {
        $label = '<label';

        if ($id !== '')
        {
            $label .= ' for="'.$id.'"';
        }

        if (is_array($attributes) && count($attributes) > 0)
        {
            foreach ($attributes as $key => $val)
            {
                $label .= ' '.$key.'="'.$val.'"';
            }
        }

        return $label.'>'.$label_text.'</label>';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset'))
{
    /**
     * Fieldset Tag
     *
     * Apre un fieldset
     * 
     * Usato per produrre <fieldset><legend>text</legend>. Chiudere con la function
     * form_fieldset_close()
     *
     * @param  String   Testo legend
     * @param  Array    Attributi
     * 
     * @return String
     */
    function form_fieldset($legend_text = '', $attributes = array())
    {
        $fieldset = '<fieldset'._attributes_to_string($attributes).">\n";
        if ($legend_text !== '')
        {
            return $fieldset.'<legend>'.$legend_text."</legend>\n";
        }

        return $fieldset;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_fieldset_close'))
{
    /**
     * Fieldset Close Tag
     * 
     * Chiude il campo fieldset
     *
     * @param String $extra	Stringa extra appesa
     * 
     * @return String
     */
    function form_fieldset_close($extra = '')
    {
        return '</fieldset>'.$extra;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_close'))
{
    /**
     * Form Close Tag
     *
     * @param String $extra	Stringa extra appesa
     * 
     * @return String
     */
    function form_close($extra = '')
    {
        return '</form>'.$extra;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_prep'))
{
    /**
     * Form Prep
     *
     * Formatta di testo in modo che possono essere posizionati in modo sicuro in un campo di modulo in caso che ha tag HTML.
     *
     * @param   String|string[]  $str	   Value to escape
     * @param   Bool             $is_textarea  Whether we're escaping for a textarea element
     * 
     * @return String
     */
    function form_prep($str = '', $is_textarea = FALSE)
    {
        if (is_array($str))
        {
            foreach (array_keys($str) as $key)
            {
                $str[$key] = form_prep($str[$key], $is_textarea);
            }

            return $str;
        }

        if ($is_textarea === TRUE)
        {
            return str_replace(array('<', '>'), array('&lt;', '&gt;'), stripslashes($str));
        }

        return str_replace(array("'", '"'), array('&#39;', '&quot;'), stripslashes($str));
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_value'))
{
    /**
     * Form Value
     *
     * Ottiene un valore dalla matrice POST per il campo specificato in modo da poter
     * ripopolare un campo di immissione o textarea . Se Form Validation
     * È attiva recupera le informazioni dalla classe di convalida
     *
     * @param  String   $field       Nome del campo
     * @param  String   $default     Default value
     * @param  Bool     $is_textarea Indica se è una textarea
     * 
     * @return String
     */
    function set_value($field = '', $default = '', $is_textarea = FALSE)
    {
        $kernel = getApplicationKernel();

        $value  = (isset($kernel->form_validation) && is_object($kernel->form_validation) && $kernel->form_validation->has_rule($field))
                    ? $kernel->form_validation->set_value($field, $default)
                    : $kernel->httprequest->get($field, NULL);

        return form_prep($value === NULL ? $default : $value, $is_textarea);
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_select'))
{
    /**
     * Set Select
     *
     * Impostate il valore selezionato di un menu <select > tramite dati dell'array POST .
     * Se Form Validation è attivo recupera le informazioni dalla classe di convalida
     *
     * @param   String $field   Nome del campo
     * @param   String $value   valore selzionato
     * @param   Bool   $default valore default
     * 
     * @return  String
     */
    function set_select($field = '', $value = '', $default = FALSE)
    {
        $kernel         = getApplicationKernel();

        if (isset($kernel->form_validation) && is_object($kernel->form_validation) && $kernel->form_validation->has_rule($field))
        {
                return $kernel->form_validation->set_select($field, $value, $default);
        }
        elseif (($input = $kernel->httprequest->getPost()->getIndex($field, FALSE)) === NULL)
        {
                return ($default === TRUE) ? ' selected="selected"' : '';
        }

        $value = (string) $value;
        if (is_array($input))
        {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($input as &$v)
            {
                if ($value === $v)
                {
                    return ' selected="selected"';
                }
            }

            return '';
        }

        return ($input === $value) ? ' selected="selected"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_checkbox'))
{
    /**
     * Set Checkbox
     *
     * Permette di impostare il valore selezionato di una casella di controllo tramite il valore della matrice POST .
     * Se Form Validation è attivo recupera le informazioni dalla classe di convalida
     *
     * @param String $field   Nome del campo
     * @param String $value   valore selzionato
     * @param Bool   $default valore default
     * 
     * @return String
     */
    function set_checkbox($field = '', $value = '', $default = FALSE)
    {
        $kernel  = getApplicationKernel();

        if (isset($kernel->form_validation) && is_object($kernel->form_validation))
        {
            return $kernel->form_validation->set_checkbox($field, $value, $default);
        }
        elseif (($input = $kernel->httprequest->getPost()->getIndex($field, FALSE)) === NULL)
        {
            return ($default === TRUE) ? ' checked="checked"' : '';
        }

        $value = (string) $value;
        if (is_array($input))
        {
            // Note: in_array('', array(0)) returns TRUE, do not use it
            foreach ($input as &$v)
            {
                if ($value === $v)
                {
                        return ' checked="checked"';
                }
            }

            return '';
        }

        return ($input === $value) ? ' checked="checked"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('set_radio'))
{
    /**
     * Set Radio
     *
     * Imposta il valore selezionato di un campo radio tramite informazioni nell'array POST .
     * Se Form Validation è attivo recupera le informazioni dalla classe di convalida
     *
     * @param String $field   Nome del campo
     * @param String $value   valore selzionato
     * @param Bool   $default valore default
     * 
     * @return String
     */
    function set_radio($field = '', $value = '', $default = FALSE)
    {
        $kernel = getApplicationKernel();

        if (isset($kernel->form_validation) && is_object($kernel->form_validation) && $kernel->form_validation->has_rule($field))
        {
            return $kernel->form_validation->set_checkbox($field, $value, $default);
        }
        elseif (($input = $kernel->httprequest->getPost()->getIndex($field, FALSE)) === NULL)
        {
            return ($default === TRUE) ? ' checked="checked"' : '';
        }

        return ($input === (string) $value) ? ' checked="checked"' : '';
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('validation_errors'))
{
    /**
     * Validation Error String
     *
     * Restituisce tutti gli errori associati a un modulo di presentazione . Questo è un helper
     * Funzione per la classe validazione dei form .
     *
     * @param String $prefix Prefisso
     * @param String $suffix Suffisso
     * 
     * @return String
     */
    function validation_errors($prefix = '', $suffix = '')
    {
        if(isset($kernel->form_validation))
        {
            return $kernel->form_validation->error_string($prefix, $suffix);
        }

        return false;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_parse_form_attributes'))
{
    /**
     * Analizza gli attributi del form
     *
     * Funzione Helper utilizzato da alcuni degli aiutanti di forma
     *
     * @param Array $attributes	Lista degli attributi
     * @param Array $default	Default values
     * 
     * @return Atring
     */
    function _parse_form_attributes($attributes, $default)
    {
        if (is_array($attributes))
        {
            foreach ($default as $key => $val)
            {
                if (isset($attributes[$key]))
                {
                    $default[$key] = $attributes[$key];
                    unset($attributes[$key]);
                }
            }

            if (count($attributes) > 0)
            {
                $default = array_merge($default, $attributes);
            }
        }

        $att = '';

        foreach ($default as $key => $val)
        {
            if ($key === 'value')
            {
                $val = form_prep($val);
            }
            elseif ($key === 'name' && ! strlen($default['name']))
            {
                continue;
            }

            $att .= $key.'="'.$val.'" ';
        }

        return $att;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('_attributes_to_string'))
{
    /**
     * Trasforma gli attributi in stringa
     *
     * Funzione di supporto utilizzato da alcuni degli aiutanti di forma
     *
     * @param Mixed $attributes attributi
     * 
     * @return String
     */
    function _attributes_to_string($attributes)
    {
        if (empty($attributes))
        {
            return '';
        }

        if (is_object($attributes))
        {
            $attributes = (array) $attributes;
        }

        if (is_array($attributes))
        {
            $atts = '';

            foreach ($attributes as $key => $val)
            {
               $atts .= ' '.$key.'="'.$val.'"';
            }

            return $atts;
        }

        if (is_string($attributes))
        {
            return ' '.$attributes;
        }

        return FALSE;
    }
}

// ------------------------------------------------------------------------

if ( ! function_exists('form_error'))
{
    /**
     * Form Error
     *
     * Restituisce l'errore per un campo di forma specifica . Questo è un aiuto per la
     * Classe validazione dei form .
     *
     * @param String $field  Nome campo
     * @param String $prefix Prefisso errore
     * @param String $suffix Suffisso 
     * 
     * @return String
     */
    function form_error($field,$prefix = '<div class="help-block with-errors">',$suffix = '</div>')
    {
        $kernel = getApplicationKernel();
        
        if(isset($kernel->form_validation) && $kernel->form_validation->error_field($field))
        {
            return $prefix.'<ul class="list-unstyled"><li>'.$kernel->form_validation->error_field($field).'</li></ul>'.$suffix;
        }
        
        return $prefix.$suffix;
    }
}


// ------------------------------------------------------------------------

if ( ! function_exists('form_has_error'))
{
    /**
     * Indica se un campo ha un errore
     *
     * @param String $field  Nome campo
     * 
     * @return Boolean
     */
    function form_has_error($field)
    {
        $kernel = getApplicationKernel();
        
        if(isset($kernel->form_validation) && $kernel->form_validation->error_field($field))
        {
            return true;
        }
        
        return false;
    }
}
