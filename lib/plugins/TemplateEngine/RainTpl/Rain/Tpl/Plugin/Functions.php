<?php

namespace Rain\Tpl\Plugin;

class Functions extends \Rain\Tpl\Plugin
{

    protected $hooks = array('beforeParse');

    /**
     * Replace flash() tag sintax ,es: { flash("<string>") }
     * @param \ArrayAccess $context
     */
    public function beforeParse(\ArrayAccess $context)
    {

        // set variables
        $code = $context->code;


        // Print flash data
        if (preg_match('/\{{(.*)}}/', $code, $matches))
        {
            $code = preg_replace(Array('/\{{(.*)}}/'), Array('<?=$1;?>'), $code);
        }

        $context->code = $code;
    }

}
