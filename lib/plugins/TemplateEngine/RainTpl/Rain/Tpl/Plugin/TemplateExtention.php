<?php

namespace Rain\Tpl\Plugin;

class TemplateExtention extends \Rain\Tpl\Plugin
{

    const REGEX_MASTER = '^(\s*|\n|\t*){\s*extends\s*[\'|"]*(?<name>.*?)[\'|"]\s*}';
    const REGEX_SECTION_BEGINING = '{\s*section\s*[\'|"](?<name>.*?)[\'|"]\s*}';
    const REGEX_SECTION_FULL = '[\s*|\n|\t*]{\s*section\s*[\'|"](?<name>.*?)[\'|"]\s*}(?<content>.*?){\s*endsection\s*}';

    protected $hooks = array('beforeParse');
    protected $sections = array();

    public function beforeParse(\ArrayAccess $context)
    {
        try
        {
            $master_filename = $this->parseMasterTemplateName($context->code);
            $sections = $this->parseTemplateSections($context->code);
            $master_code = $this->loadMasterTemplate($context, $master_filename);
            $this->replaceSectionsIntoMaster($master_code, $sections);
            $context->code = $master_code;
        }
        catch (\Rain\Tpl\SyntaxException $nfe)
        {
            return;
        }
    }

    protected function parseMasterTemplateName($html)
    {
        if (preg_match('%' . self::REGEX_MASTER . '%is', $html, $master) == 0)
            throw new \Rain\Tpl\SyntaxException('{extends \'<template>\'} not found)'); //can check if there is sections, if so throw exception. No extends sections found.... maybe next version! 

        $master = trim($master['name']); //returns false if it's empty

        if ($master === false)
        {
            throw new \Rain\Tpl\SyntaxException('Invalid master template name');
        }

        return $master;
    }

    protected function parseTemplateSections($html)
    {
        if (preg_match_all('%' . self::REGEX_SECTION_FULL . '%is', $html, $matches, PREG_SET_ORDER) == 0)
            return;

        foreach ($matches as $match)
        {
            if (!is_array($match))
            {
                continue;
            }
            $section_name = trim($match['name']); // returns false if it's empty
            $content = trim($match['content']);
            if ($section_name == false)
            {
                throw new Exception('Invalid name for ' . $match);
                exit;
            }
            else if (!$content && preg_match('%' . self::REGEX_SECTION_BEGINING . '%is', $content) != 0)
            {
                throw new Exception('Nested {section \'xxx\'} is not allowed in this version.');
                exit;
            }
            $sections[$section_name] = ($content) ? $content : '';
        }
        unset($matches, $match);
        return $sections;
    }

    protected function loadMasterTemplate($context, $master_filename)
    {
        // Make directories to array for multiple template directory
        $templateDirectories = $context->conf['tpl_dir'];
        if (!is_array($templateDirectories))
        {
            $templateDirectories = array($templateDirectories);
        }

        $master_filename = basename($master_filename);
        $isFileNotExist = true;
        foreach ($templateDirectories as $template_directory)
        {
            $templateFilepath = $template_directory . $master_filename . '.' . $context->conf['tpl_ext'];
            // For check templates are exists
            if (file_exists($templateFilepath))
            {
                $isFileNotExist = false;
                break;
            }
        }

        // if the template doesn't exsist throw an error
        if ($isFileNotExist === true)
        {
            throw new \Rain\Tpl\Exception('Master template \'' . $master_filename . '\' not found at ' . $templateFilepath);
        }

        $fo = fopen($templateFilepath, 'r');
        if (!flock($fo, LOCK_SH))
        {
            throw new \Rain\Tpl\Exception('Unable to get the lock for ' . $templateFilepath);
        }
        else
        {
            $master_code = fread($fo, filesize($templateFilepath));
            flock($fo, LOCK_UN);
            fclose($fo);
            return $master_code;
        }
    }

    protected function replaceSectionsIntoMaster(&$code, $sections)
    {

        if (is_array($sections) && count($sections) > 0)
        {
            foreach ($sections as $key => $value)
            {
                $code = preg_replace('%{\s*section\s*[\'|"]' . $key . '[\'|"]\s*}%is', $sections[$key], $code, -1);
            }

            //It replaces sections withtout defined data, with empty
            $code = preg_replace('%' . self::REGEX_SECTION_BEGINING . '%is', '', $code, -1);
        }
        else
        {
            throw new \Rain\Tpl\Exception('Unable to get the sections inside template!');
        }
    }

}
