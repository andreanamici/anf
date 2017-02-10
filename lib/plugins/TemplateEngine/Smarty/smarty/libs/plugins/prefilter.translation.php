<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     prefilter.pre01.php
 * Type:     prefilter
 * Name:     pre01
 * Purpose:  Convert html tags to be lowercase.
 * -------------------------------------------------------------
 */

/**
 * Traduce un testo
 * @param String $source
 * @param Smarty $smarty
 * @return String
 */
function smarty_prefilter_translation($source, &$smarty)
{     
    $translationModifierAndDomaninPattern = '/\{\@([\w\s]+)\@\|(.*?)\,[\"|\']([\w]+)[\"|\']\}/';
    
    if (preg_match_all($translationModifierAndDomaninPattern, $source))
    {
        $source = preg_replace_callback($translationModifierAndDomaninPattern, function($matches)
        {
            $mofiers = "|" . $matches[2];
            $phpCode = \Rain\Tpl::modifierReplace("translate('{$matches[1]}','{$matches[3]}')" . $mofiers);
            return "<?=" . $phpCode . "; ?>";
        }, $source);
    }

    $translationModifierPattern = '/\{\@[\"|\']{0,}([\w\s]+)[\"|\']{0,}\@\|(.*?)\}/';

    if (preg_match_all($translationModifierPattern, $source))
    {
        $source = preg_replace_callback($translationModifierPattern, function($matches)
        {
            $mofiers = "|" . $matches[2];
            $phpCode = \Rain\Tpl::modifierReplace("translate('{$matches[1]}')" . $mofiers);
            return "<?=" . $phpCode . "; ?>";
        }, $source);
    }

    $translationAndDomaniPattern = '/\{\@[\"|\']{0,}([\w\s]+)[\"|\']{0,}\@\,[\"|\']([\w]+)[\"|\']\}/';

    if (preg_match($translationAndDomaniPattern, $source))
    {
        $source = preg_replace(Array($translationAndDomaniPattern), Array('<?=translate(\'$1\',\'$2\');?>'), $source);
    }

    $basicTranslationPattern = '/\{\@[\"|\']{0,1}(.*?)[\"|\']{0,1}\@\}/';

    if (preg_match($basicTranslationPattern, $source))
    {
        $source = preg_replace(Array($basicTranslationPattern), Array('<?=translate("$1");?>'), $source);
    }


    return $source;
}
