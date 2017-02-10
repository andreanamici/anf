<?php

namespace Rain\Tpl\Plugin;

class FlashData extends \Rain\Tpl\Plugin
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
        if (preg_match('/\{flash\(\"([A-z0-9\_\-]+)\"\)\}/', $code, $matches))
        {
            $code = preg_replace(Array('/\{flash\(\"([A-z0-9\_\-]+)\"\)\}/'), Array('<? flash("$1"); ?>'), $code);
        }

        // Print flash data
        if (preg_match('/\{flash\(\'([A-z0-9\_\-]+)\'\)\}/', $code, $matches))
        {
            $code = preg_replace(Array('/\{flash\(\'([A-z0-9\_\-]+)\'\)\}/'), Array('<? flash("$1"); ?>'), $code);
        }

        $context->code = $code;
    }

}
