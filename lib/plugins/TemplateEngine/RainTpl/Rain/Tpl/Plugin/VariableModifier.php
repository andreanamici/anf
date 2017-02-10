<?php

namespace Rain\Tpl\Plugin;

class VariableModifier extends \Rain\Tpl\Plugin
{

    protected $hooks = array('beforeParse');

    /**
     * Apply modifier like smarty
     * @param \ArrayAccess $context
     */
    public function beforeParse(\ArrayAccess $context)
    {

        // set variables
        $code = $context->code;

        if (preg_match_all('/\{\$([A-z0-9\_]+)\|(.*?)\}/', $code, $matches))
        {
            $code = preg_replace_callback('/\{\$([A-z0-9\_]+)\|(.*?)\}/', function($matches)
            {
                $variable  = "$".$matches[1];
                $mofiers   = "|" . $matches[2];
                $phpCode   = \Rain\Tpl::modifierReplace($variable . $mofiers);
                return "<?=" . $phpCode . "; ?>";
            }, $code);
        }

        $context->code = $code;
    }

}
