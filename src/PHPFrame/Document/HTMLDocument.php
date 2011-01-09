<?php
/**
 * PHPFrame/Document/HTMLDocument.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * HTML Document Class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_HTMLDocument extends PHPFrame_XMLDocument
{
    /**
     * The document body
     *
     * @var string
     */
    private $_body;
    /**
     * DOM Document Type object
     *
     * @var DOMDocumentType
     */
    private $_doctype = null;
    /**
     * An array containing meta tags.
     *
     * @var array
     */
    private $_meta_tags = array();
    /**
     * An array containing scripts.
     *
     * @var array
     */
    private $_scripts = array();
    /**
     * An array containing style sheets.
     *
     * @var array
     */
    private $_style_sheets = array();
    /**
     * An array containing links to RSS/Atom feeds.
     *
     * @var array
     */
    private $_feed_links = array();
    /**
     * If set to TRUE object will only include contents of the body tag. This
     * is used for AJAX output.
     *
     * @var bool
     */
    private $_body_only = false;

    /**
     * Constructor
     *
     * @param string          $mime    [Optional] Default value is 'text/html'.
     * @param string          $charset [Optional] Default value is 'UTF-8'.
     * @param DOMDocumentType $doctype [Optional] Default doctype used is XHTML
     *                                 1.0 Strict.
     *
     * @return void
     * @uses   DOMImplementation, PHPFrame_URI
     * @since  1.0
     */
    public function __construct(
        $mime="text/html",
        $charset=null,
        DOMDocumentType $doctype=null
    ) {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);

        // Add generator meta tag
        $this->addMetaTag("generator", "PHPFrame");

        // Get default doctype if not passed
        $this->doctype($doctype);
    }

    /**
     * Convert object to string
     *
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        // If "body only" mode we simply return the body
        if ($this->bodyOnly()) {
            return $this->body();
        }

        // Add title tag in head node
        $head_node = $this->dom()->getElementsByTagName("head")->item(0);

        $title = $this->title();
        if ($title) {
            $this->addNode("title", $head_node, null, $title);
        }

        // Add meta tags
        foreach ($this->_meta_tags as $meta_tag) {
            $meta_node = $this->addNode("meta", $head_node);

            // Add name attribute if any
            if (!empty($meta_tag["name"])) {
                $this->addNodeAttr($meta_node, "name", $meta_tag["name"]);
            }
            // Add http_equiv attribute if any
            if (!empty($meta_tag["http_equiv"])) {
                $this->addNodeAttr(
                    $meta_node,
                    "http-equiv",
                    $meta_tag["http_equiv"]
                );
            }

            // Add content attribute
            $this->addNodeAttr($meta_node, "content", $meta_tag["content"]);
        }

        // Add feed links
        foreach ($this->_feed_links as $feed_link_attr) {
            $this->addNode("link", $head_node, $feed_link_attr);
        }

        // Add styles
        foreach ($this->_style_sheets as $style_sheet_attr) {
            $this->addNode("link", $head_node, $style_sheet_attr);
        }

        // Add scripts
        foreach ($this->_scripts as $script_attr) {
            // Create script tag node
            if ($this->isHTML5()) {
                unset($script_attr["type"]);
            }

            $in_head = $script_attr["in_head"];
            unset($script_attr["in_head"]);
            if ($in_head) {
                $this->addNode("script", $head_node, $script_attr, "//");
            } else {
                $body_node = $this->dom()->getElementsByTagName("body")->item(0);
                $this->addNode("script", $body_node, $script_attr, "//");
            }
        }

        // Render dom using parent's __toString() method
        if ($this->useBeautifier()) {
            // Render dom as XML
            $str = $this->dom()->saveXML();

            // First remove xml declaration
            $xmldecl = substr($str, 0, strpos($str, "\n"));
            $str     = substr($str, strlen($xmldecl)+1);

            // Split the string so that we can pass the XML to the beautifier
            // and print the doctype separately
            $doctype = substr($str, 0, strpos($str, "\n"));
            $html    = substr($str, strpos($str, "\n")+1);
            $options = array(
               "removeLineBreaks"   => true,
               "removeLeadingSpace" => true,       // not implemented, yet
               "indent"             => "  ",
               "linebreak"          => "\n",
               "caseFolding"        => false,
               "caseFoldingTo"      => "lowercase",
               "normalizeComments"  => false,
               "maxCommentLine"     => -1,
               "multilineTags"      => false
            );
            $beautifier = new XML_Beautifier($options);
            $html       = $beautifier->formatString($html);
            $html       = $doctype."\n".$html;
        } else {
            $this->dom()->formatOutput = true;
            $html = $this->dom()->saveHTML();
        }

        if ($this->isHTML5()) {
            $html = $this->_ieConditionalStyles($html);
        }

        // Add body and return
        return str_replace("{content}", "\n".$this->body()."\n", $html);
    }

    /**
     * Get/set the document body.
     *
     * @param string $str String containing the document body.
     *
     * @return string
     * @since  1.0
     */
    public function body($str=null)
    {
        if (!is_null($str)) {
            $this->_body = (string) $str;
        }

        return (string) $this->_body;
    }

    /**
     * Append string to the document body
     *
     * @param string $str String to append the document body.
     *
     * @return void
     * @since  1.0
     */
    public function appendBody($str)
    {
        $this->_body .= (string) $str;
    }

    /**
     * Prepend string to the document body
     *
     * @param string $str String to prepend to the document body.
     *
     * @return void
     * @since  1.0
     */
    public function prependBody($str)
    {
        $this->_body = (string) $str.$this->_body;
    }

    /**
     * Get/set DOM Document Type object. If not set yet a default one will be
     * created with the following values:
     * - public_id : -//W3C//DTD XHTML 1.0 Strict//EN
     * - system_id: http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd
     *
     * @param DOMDocumentType $doctype [Optional]
     *
     * @return DOMDocumentType
     * @since  1.0
     */
    public function doctype(DOMDocumentType $doctype=null)
    {
        if (!is_null($doctype)) {
            $this->_doctype = $doctype;
            $this->_initDOM($this->_doctype);
        }

        // Create new doc type object if we don't have one yet
        if (!($this->_doctype instanceof DOMDocumentType)) {
            $imp = new DOMImplementation();
            $this->_doctype = $imp->createDocumentType(
                "html",
                "-//W3C//DTD XHTML 1.0 Strict//EN",
                "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
            );
            $this->_initDOM($this->_doctype);
        }

        return $this->_doctype;
    }

    /**
     * Add meta tag
     *
     * @param string $name       This attribute identifies a property name. This
     *                           specification does not list legal values for
     *                           this attribute.
     * @param string $content    This attribute specifies a property's value.
     *                           This specification does not list legal values
     *                           for this attribute.
     * @param string $http_equiv This attribute may be used in place of the name
     *                           attribute. HTTP servers use this attribute to
     *                           gather information for HTTP response message
     *                           headers.
     *
     * @return void
     * @since  1.0
     * @todo   This method should check whether the meta tag has already been
     *         added to avoid printing the same meta tag twice.
     */
    public function addMetaTag($name, $content, $http_equiv=null)
    {
        $this->_meta_tags[] = array(
            "name"       => (string) $name,
            "content"    => (string) $content,
            "http_equiv" => (string) $http_equiv
        );
    }

    /**
     * Add linked scrip in document head
     *
     * It takes both relative and absolute values.
     *
     * @param string $src     The relative or absolute URL to the script source.
     * @param string $type    The script type. Default is text/javascript.
     * @param bool   $in_head [Optional] Bool indicating whether script should
     *                        be added in the head node or at the end of the
     *                        body. Default value is TRUE.
     *
     * @return void
     * @since  1.0
     * @todo   This method should check whether the script has already been
     *         added to avoid loading the same script twice.
     */
    public function addScript($src, $type="text/javascript", $in_head=true)
    {
        $this->_scripts[] = array(
            "src"     => (string) $src,
            "type"    => (string) $type,
            "in_head" => (bool) $in_head
        );
    }

    /**
     * Attach external stylesheet.
     *
     * @param string $href  The relative or absolute URL to the link source.
     * @param string $type  The link type. Default is text/css.
     * @param string $media This attribute specifies the intended device. It
     *                      may be a single media descriptor or a csv list. The
     *                      default value for this attribute is "screen".
     *
     * @return void
     * @since  1.0
     */
    public function addStyleSheet($href, $type="text/css", $media="screen")
    {
        $this->_style_sheets[] = array(
            "rel"   => "stylesheet",
            "href"  => (string) $href,
            "type"  => (string) $type,
            "media" => (string) $media
        );
    }

    /**
     * Add link to RSS/Atom feed.
     *
     * @param string $href  The relative or absolute URL to the link source.
     * @param string $title The feed title.
     * @param string $type  The link type. Default is application/rss+xml.
     *
     * @return void
     * @since  1.0
     */
    public function addRSSLink($href, $title, $type="application/rss+xml")
    {
        $this->_feed_links[] = array(
            "rel"   => "alternate",
            "href"  => $href,
            "title" => $title,
            "type"  => $type
        );
    }

    /**
     * Apply theme
     *
     * @param string               $theme_url  URL to theme.
     * @param string               $theme_path Absolute path to theme template
     *                                         in filesystem.
     * @param PHPFrame_Application $app        Reference to application.
     *
     * @return void
     * @since  1.0
     */
    public function applyTheme(
        $theme_url,
        $theme_path,
        PHPFrame_Application $app
    ) {
        // Start buffering
        ob_start();
        include_once $theme_path;
        // save buffer in body
        $str = ob_get_contents();
        // clean output buffer
        ob_end_clean();

        $this->body($str);
    }

    /**
     * Get/set the "body only" flag. If set to TRUE object will only include
     * contents of the body tag. This is used for AJAX output.
     *
     * @param bool $bool [Optional] Whether or not to render as "body only".
     *
     * @return void
     * @since  1.0
     */
    public function bodyOnly($bool=null)
    {
        if (!is_null($bool)) {
            $this->_body_only = (bool) $bool;
        }

        return $this->_body_only;
    }

    /**
     * Is the document HTML5?
     *
     * @return bool
     * @since  1.0
     */
    public function isHTML5()
    {
        $doctype = $this->doctype();

        if ($doctype->name == "html"
            && empty($doctype->publicId)
            && empty($doctype->systemId)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Initialise DOM. This needs to be done every time the doctype is set.
     *
     * @param DOMDocumentType $doctype Doctype object.
     *
     * @return void
     * @since  1.0
     */
    private function _initDOM(DOMDocumentType $doctype)
    {
        // Acquire DOM object of HTML type
        $imp = new DOMImplementation();
        $this->dom($imp->createDocument(null, "html", $doctype));

        // Get root node
        $html_node = $this->dom()->getElementsByTagName("html")->item(0);

        // Add head
        $head_node = $this->addNode("head", $html_node);
        // Add body
        $this->addNode("body", $html_node, null, "\n{content}\n");

        // Add charset meta tag
        foreach ($this->_meta_tags as $key=>$value) {
            if ($value["http_equiv"] == "Content-Type") {
                unset($this->_meta_tags[$key]);
            }
        }

        if ($this->isHTML5()) {
            $this->addNode("meta", $head_node, array("charset"=>$this->charset()));
        } else {
            $this->addMetaTag(
                null,
                $this->mime()."; charset=".$this->charset(),
                "Content-Type"
            );
        }

        // Add base url
        $uri = new PHPFrame_URI();
        $this->addNode("base", $head_node, array("href"=>$uri->getBase()));
    }

    /**
     * Replace <html> tag with Paul Irish's IE conditional hack:
     * paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/
     *
     * @param string $html The HTML document as a string.
     *
     * @return string
     */
    private function _ieConditionalStyles($html)
    {
        preg_match("/<html(\b[^>]*)>/", $html, $matches);
        $html_node = $matches[1];
        preg_match_all("/([a-zA-Z]+)=\"([^\"]+)\"/", $html_node, $matches);

        $original_class_attr = "";
        $class = "";
        $other_attr = "";
        for ($i=0; $i<count($matches[1]); $i++) {
            if ($matches[1][$i] == "class") {
                $class = $matches[2][$i]." ";
                $original_class_attr = "class=\"".$matches[2][$i]."\"";
            } else {
                $other_attr .= " ".$matches[1][$i]."=\"".$matches[2][$i]."\"";
            }
        }

        $replace = "<!--[if lt IE 7 ]> <html class=\"".$class."ie6\"".$other_attr."> <![endif]-->
<!--[if IE 7 ]>    <html class=\"".$class."ie7\"".$other_attr."> <![endif]-->
<!--[if IE 8 ]>    <html class=\"".$class."ie8\"".$other_attr."> <![endif]-->
<!--[if IE 9 ]>    <html class=\"".$class."ie9\"".$other_attr."> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html ".$original_class_attr.$other_attr."> <!--<![endif]-->";

        return preg_replace("/<html\b[^>]*>/", $replace, $html);
    }
}
