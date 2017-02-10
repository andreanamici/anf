<?php

namespace Rain\Tpl\Plugin;

class UrlGenerator extends \Rain\Tpl\Plugin
{

    protected $hooks = array('beforeParse');

    /**
     * Replace url() e path() tag sintax ,es: { url("<action>",Array()) },{ path("<action>",Array()) }
     * @param \ArrayAccess $context
     */
    public function beforeParse(\ArrayAccess $context)
    {

        // set variables
        $code = $context->code;


        if (preg_match('/\{url\((.*?)\)\}/', $code, $matches))
        {
            $code = preg_replace(Array('/\{[\s]{0,}url\((.*?)\)[\s]{0,}\}/'), Array('<?php echo url($1); ?>'), $code);
        }

        if (preg_match('/\{path\((.*?)\)\}/', $code, $matches))
        {
            $code = preg_replace(Array('/\{[\s]{0,}path\((.*?)\)[\s]{0,}\}/'), Array('<?php echo path($1); ?>'), $code);
        }

        $context->code = $code;
    }

}
