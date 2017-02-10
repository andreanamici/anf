<?php

namespace Rain\Tpl\Plugin;

class Translation extends \Rain\Tpl\Plugin
{

    protected $hooks                    = array('beforeParse');
   
    protected static $translateFunction = "translate";
    
    /**
     * replace the path of image src, link href and a href.
     * @param \ArrayAccess $context
     */
    public function beforeParse(\ArrayAccess $context)
    {   
        // set variables
        $code = $context->code;
       
        $translationModifierAndDomainPattern = '/\{\@[\"|\']{0,}(.*?)[\"|\']{0,}\@\|(.*?)\,[\"|\']([\w]+)[\"|\']\}/';
        
        if (preg_match_all($translationModifierAndDomainPattern, $code))
        {
            $code = preg_replace_callback($translationModifierAndDomainPattern, function($matches)
            {
                $translateValue     = $matches[1];
                $domain             = $matches[3];
                $modifiers          = "|" . $matches[2];
                
                $phpCode = \Rain\Tpl::modifierReplace(Translation::$translateFunction."('{$translateValue}','{$domain}')" . $modifiers);
                return "<?=" . $phpCode . "; ?>";
            }, $code);
        }
 
        $translationModifierPattern = '/\{\@[\"|\']{0,1}(.*?)[\"|\']{0,1}\@\|(.*?)\}/';
        
        if (preg_match_all($translationModifierPattern, $code))
        {
            $code = preg_replace_callback($translationModifierPattern, function($matches)
            {
                $translateValue = addslashes($matches[1]);
                $modifiers = "|" . $matches[2];

                $phpCode = \Rain\Tpl::modifierReplace(Translation::$translateFunction."('{$translateValue}')" . $modifiers);
                
                return "<?=" . $phpCode . "; ?>";
                
            }, $code);
        }
        
        $translationAndDomainPattern = '/\{\@[\"|\']{0,}(.*?)[\"|\']{0,}\@\,[\"|\']([\w]+)[\"|\']\}/';
       
        if (preg_match($translationAndDomainPattern, $code,$matches))
        {
            $code = preg_replace_callback($translationAndDomainPattern,function($matches){
                
                $translateValue = addslashes($matches[1]);
                $domain         = $matches[2];
                
                return "<?=".Translation::$translateFunction."('{$translateValue}','{$domain}');?>";
                
            }, $code);
        }
        
        $basicTranslationPattern = '/\{\@[\"|\']{0,1}(.*?)[\"|\']{0,1}\@\}/';
        
        if (preg_match($basicTranslationPattern, $code,$matches))
        {
            $code = preg_replace_callback($basicTranslationPattern, function($matches){ 
                
                $translateValue = addslashes($matches[1]);

                return "<?=".Translation::$translateFunction."('{$translateValue}');?>";
                
            }, $code);
        }

        $context->code = $code;
    }

}
