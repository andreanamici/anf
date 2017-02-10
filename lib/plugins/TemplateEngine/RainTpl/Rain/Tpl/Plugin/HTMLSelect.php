<?php

namespace Rain\Tpl\Plugin;

class HTMLSelect extends \Rain\Tpl\Plugin
{

    protected $hooks = array('afterParse');

    /**
     * Raplace Rain Select {select ....} 
     * @param \ArrayAccess $context
     */
    public function afterParse(\ArrayAccess $context)
    {

        // set variables
        $code = $context->code;
//        die($code);
        // match the images
        if (preg_match('/\{select(.*?)\}/', $code))
        {
            $code = preg_replace_callback('/\{select(.*?)\}/', function($matches)
            {
                $selectOptions = $matches[1];
                $matches = Array();
                $selectCode = "";
                $for = preg_match("/for=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $class = preg_match("/class=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $name = preg_match("/name=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $id = preg_match("/id=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $selected = preg_match("/selected=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : '-1';
                $opt_def_value = preg_match("/option_default_value=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $opt_def_text = preg_match("/option_default_text=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;
                $opt_format = preg_match("/option_format_string=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : false;

                $opt_format = str_replace(Array("<?=", ";?>"), Array("", ""), $opt_format);

                if (!$opt_format)
                {
                    $opt_format = "''";
                }
                else if (!preg_match("/translate\(/", $opt_format))
                {
                    $opt_format = '"' . $opt_format . '"';
                }

                if (!preg_match("/\\$([A-Za-z0-9\_]+)/", $selected))
                {
                    $selected = "'{$selected}'";
                }
                else
                {
                    $selected = "$selected";
                }

                $selectCode.= "<select ";

                if ($name !== false)
                {
                    $selectCode.=" name='{$name}' ";
                }

                if ($class !== false)
                {
                    $selectCode.=" class='{$class}' ";
                }

                if ($id !== false)
                {
                    $selectCode.=" id='{$id}' ";
                }

                $selectCode.=">";

                if ($opt_def_value !== false && $opt_def_text !== false)
                {
                    $selectCode.="\n\t\t\t\t\t\t\t\t\t\t\t\t<option value='{$opt_def_value}'>{$opt_def_text}</option>";
                }

                if ($for !== false)
                {

                    switch ($for)
                    {
                        case 'month':

                            $selectCode.="
                                                <?php
                                                   \$months = Utility_CommonFunction::Date_getMonthsArray();
                                                   foreach(\$months as \$key => \$value)
                                                   {
                                                      \$sel        = $selected == \$key ? 'selected' : '';
                                                      \$text       = $opt_format;
                                                      if(strlen(\$text) >0){
                                                          \$text = strstr(\$text,'%s')!==false ? sprintf(\$text,\$value) : \$text.' '.\$value;
                                                      }else{
                                                          \$text = \$value;
                                                      }
                                                   ?>   
                                                   <option value='<?php echo \$key;?>' <?php echo \$sel;?>><?php echo \$text;?></option>
                                                   <?php
                                                   }
                                                   ?>";

                            break;
                        case 'range':
                            $from = preg_match("/from=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : 0;
                            $to = preg_match("/to=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : 0;
                            $inc = preg_match("/increment=['|\"]([^\"]+)['|\"]/", $selectOptions, $matches) > 0 ? $matches[1] : 1;
                            $gt = $inc > 0 ? "<" : ">";

                            $selectCode.="
                                                <?php                                                  
                                                   for(\$i = {$from};\$i {$gt} {$to};\$i+={$inc})
                                                   {
                                                      \$sel        = $selected == \$i ? 'selected' : '';
                                                      \$text       = $opt_format;
                                                      if(strlen(\$text) >0){
                                                          \$text = strstr(\$text,'%s')!==false ? sprintf(\$text,\$i) : \$text.' '.\$i;
                                                      }else{
                                                          \$text = \$i;
                                                      }
                                                      
                                                   ?>   
                                                   <option value='<?php echo \$i;?>' <?php echo \$sel;?>><?php echo \$text;?></option>
                                                   <?php
                                                   }
                                                ?>";

                            break;
                        default:


                            $selectCode.="
                                                <?php 
                                                if(is_array($for) && count($for)>0)
                                                { 
                                                   foreach($for as \$key => \$value)
                                                   {
                                                      \$sel = $selected == \$key ? 'selected' : '';
                                                   ?>   
                                                   <option value='<?php echo \$key;?>' <?php echo \$sel;?>><?php echo \$value;?></option>
                                                   <?php
                                                   }
                                                }
                                                ?>";

                            break;
                    }
                }

                $selectCode.="</select>";
                return $selectCode;
            }, $code);
        }

        $context->code = $code;
    }

}
