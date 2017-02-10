<?php

namespace Rain\Tpl\Plugin;

/**
 * RainTPL plugins
 */
class PathReplace extends \Rain\Tpl\Plugin
{

    protected $hooks = array('beforeParse');
    private $tags = array('a', 'img', 'photo', 'link', 'script', 'input', 'form', 'media');

    public function setTags($tags)
    {
        $this->tags = (array) $tags;
        return $this;
    }

    /**
     * Effettua il replace dei path dei tags configurati secondo le logiche imposte dall'applicationTemplating
     * 
     *
     * @param \ArrayAccess $context
     */
    public function beforeParse(\ArrayAccess $context)
    {

        // set variables
        $html = $context->code;
        $template_basedir = $context->template_basedir;
        $tags = $this->tags;
        $basecode = $template_basedir;

        $baseurl = "<?php echo static::\$conf['base_url']; ?>";
        $baseurl_js = "<?php echo static::\$conf['base_url_js']; ?>";
        $baseurl_css = "<?php echo static::\$conf['base_url_css']; ?>";
        $baseurl_media = "<?php echo static::\$conf['base_url_media']; ?>";
        $baseurl_images = "<?php echo static::\$conf['base_url_images']; ?>";
        $baseurl_photos = "<?php echo static::\$conf['base_url_photos']; ?>";

        $exp = $sub = array();

        if (in_array("img", $tags))
        {
            $exp = array_merge($exp, array('/<img(.*?)src=(?:[\'|"])(http|https)\:\/\/([^"]+?)(?:[\'|"])/i', '/<img(.*?)src=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<img(.*?)src="([^\<?\/\{].*)"/', '/<img(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<img$1src=@$2://$3@', '<img$1src=@$2@', '<img$1src="' . $baseurl_images . '$2"', '<img$1src="$2"'));
        }

        if (in_array("photo", $tags))
        {
            $exp = array_merge($exp, array('/<photo(.*?)src=(?:[\'|"])(http|https)\:\/\/([^"]+?)(?:[\'|"])/i', '/<photo(.*?)src=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<photo(.*?)src="([^\<?\/\{].*)"/', '/<photo(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<img$1src=@$2://$3@', '<img$1src=@$2@', '<img$1src="' . $baseurl_photos . '$2"', '<img$1src="$2"'));
        }

        if (in_array("script", $tags))
        {
            $exp = array_merge($exp, array('/<script(.*?)src=(?:[\'|"])(http|https)\:\/\/([^"]+?)(?:[\'|"])/i', '/<script(.*?)src=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<script(.*?)src="([^\<?\/\{].*)"/', '/<script(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<script$1src=@$2://$3@', '<script$1src=@$2@', '<script$1src="' . $baseurl_js . '$2"', '<script$1src="$2"'));
        }

        if (in_array("link", $tags))
        {
            $exp = array_merge($exp, array('/<link(.*?)href=(?:[\'|"])^#(http|https)\:\/\/([^"]+?)(?:[\'|"])/i', '/<link(.*?)href=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<link(.*?)href="([^\<?\/\{].*)"/', '/<link(.*?)href=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<link$1href=@$2://$3@', '<link$1href=@$2@', '<link$1href="' . $baseurl_css . '$2"', '<link$1href="$2"'));
        }

        if (in_array("media", $tags))
        {
            $exp = array_merge($exp, array('/<source src=["|\'](^#.*?)["|\']/i', '/<source src=["|\'](^#.*?)["|\']/i'));
            $sub = array_merge($sub, array('<source src="$1"', '<source src="' . $baseurl_media . '@$1@"'));
        }

        if (in_array("a", $tags))
        {
            $exp = array_merge($exp, array('/<a(.*?)href=(?:[\'|"])(http:\/\/|https:\/\/|javascript:|mailto:|\/|{)([^"]+?)(?:[\'|"])/i', '/<a(.*?)href=([\'|"])([^\/#|^<\?php].*)([\'|"])/i', '/<a(.*?)href=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<a$1  href=@$2$3@', '<a$1 href=$2' . $baseurl . '$3$4', '<a$1 href="$2"'));
        }

        if (in_array("input", $tags))
        {
            $exp = array_merge($exp, array('/<input(.*?)src=(?:[\'|"])(http|https)\:\/\/([^"]+?)(?:[\'|"])/i', '/<input(.*?)src=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<input(.*?)src="([^\<?\/\{].*)"/', '/<input(.*?)src=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<input$1src=@$2://$3@', '<input$1src=@$2@', '<input$1src="' . $baseurl_images . '$2"', '<input$1src="$2"'));
        }

        if (in_array("form", $tags))
        {
            $exp = array_merge($exp, array('/<form(.*?)action=(?:[\'|"])(?:[\'|"])/i', '/<form(.*?)action=(?:[\'|"])([^"]+?)#(?:[\'|"])/i', '/<form(.*?)action="([^\<?\/\{].*)"/', '/<form(.*?)action=(?:\@)([^"]+?)(?:\@)/i'));
            $sub = array_merge($sub, array('<form=@$2://$3@', '<form$1action=@$2@', '<form$1action="' . $baseurl . '$2"', '<form$1action="$2"'));
        }

        $context->code = preg_replace($exp, $sub, $html);
    }

}

