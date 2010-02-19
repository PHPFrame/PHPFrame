<?php
/**
 * PHPFrame/Document/HTMLDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * HTML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_HTMLDocument extends PHPFrame_XMLDocument
{
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
     * If set to TRUE object will only include contents of the body tag. This 
     * is used for AJAX output.
     * 
     * @var bool
     */
    private $_body_only = false;
    
    /**
     * Constructor
     * 
     * @param string $mime      [Optional] Default value is 'text/html'.
     * @param string $charset   [Optional] Default value is 'UTF-8'.
     * @param string $public_id [Optional] Default value is 
     *                          "-//W3C//DTD XHTML 1.0 Strict//EN".
     * @param string $system_id [Optional] Default value is 
     *                          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
     * 
     * @return void
     * @uses   DOMImplementation, PHPFrame_URI, PHPFrame_Pathway
     * @since  1.0 
     */
    public function __construct(
        $mime="text/html", 
        $charset=null,
        $public_id="-//W3C//DTD XHTML 1.0 Strict//EN", 
        $system_id="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
    ) {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Acquire DOM object of HTML type
        $imp       = new DOMImplementation();
        $doc_type  = $this->getDocType($public_id, $system_id);
        
        $this->dom($imp->createDocument(null, "html", $doc_type)); 
        
        // Get root node
        $html_node = $this->dom()->getElementsByTagName("html")->item(0);
        
        // Add head
        $head_node = $this->addNode("head", $html_node);
        // Add body
        $this->addNode("body", $html_node, null, "\n{content}\n");
        
        // Add meta tags
        $this->addMetaTag("generator", "PHPFrame");
        $this->addMetaTag(
            null, 
            $this->mime()."; charset=".$this->charset(),
            "Content-Type"
        );
        
        // Add base url
        $uri = new PHPFrame_URI();
        $this->addNode("base", $head_node, array("href"=>$uri->getBase()));
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
        if ($this->_body_only) {
            return $this->getBody();
        }
        
        // Add title tag in head node 
        $head_node = $this->dom()->getElementsByTagName("head")->item(0);
        $this->addNode("title", $head_node, null, $this->getTitle());
        
        // Add meta tags
        foreach ($this->_meta_tags as $meta_tag) {
            $meta_node = $this->addNode("meta", $head_node);
            
            // Add name attribute if any
            if (!is_null($meta_tag["name"])) {
                $this->addNodeAttr($meta_node, "name", $meta_tag["name"]);
            }
            // Add http_equiv attribute if any
            if (!is_null($meta_tag["http_equiv"])) {
                $this->addNodeAttr(
                    $meta_node, 
                    "http-equiv", 
                    $meta_tag["http_equiv"]
                );
            }
            
            // Add content attribute
            $this->addNodeAttr($meta_node, "content", $meta_tag["content"]);
        }
        
        // Add scripts
        foreach ($this->_scripts as $script_attr) {
            // Create script tag node
            $this->addNode("script", $head_node, $script_attr);
        }
        
        // Add styles
        foreach ($this->_style_sheets as $style_sheet_attr) {
            $this->addNode("link", $head_node, $style_sheet_attr);
        }
        
        // Render DOM Document as HTML string
        $this->dom()->formatOutput = true;
        $html = $this->dom()->saveHTML();
        
        // Make line breaks after script tags for pretty output
        $html = preg_replace("/<\/script>/", "</script>\n", $html);
        
        // Add body and return
        return str_replace("{content}", $this->getBody(), $html);
    }
    
    /**
     * Get DOM Document Type object
     * 
     * @param string $public_id [Optional] Default value is 
     *                          "-//W3C//DTD XHTML 1.0 Strict//EN".
     * @param string $system_id [Optional] Default value is 
     *                          "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
     * 
     * @return DOMDocumentType
     * @since  1.0
     */
    public function getDocType(
        $public_id="-//W3C//DTD XHTML 1.0 Strict//EN", 
        $system_id="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
    ) {
        // Create new doc type object if we don't have one yet
        if (!($this->_doctype instanceof DOMDocumentType)) {
            $imp = new DOMImplementation;
            $this->_doctype = $imp->createDocumentType(
                "html",
                $public_id,
                $system_id
            );
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
            "name"       => $name, 
            "content"    => $content, 
            "http_equiv" => $http_equiv
        );
    }
    
    /**
     * Add linked scrip in document head
     * 
     * It takes both relative and absolute values.
     * 
     * @param string $src  The relative or absolute URL to the script source.
     * @param string $type The script type. Default is text/javascript.
     * 
     * @return void
     * @since  1.0
     * @todo   This method should check whether the script has already been 
     *         added to avoid loading the same script twice.
     */
    public function addScript($src, $type='text/javascript') 
    {
        // Make source absolute URL
        $this->_makeAbsolute($src);
        
        $this->_scripts[] = array("src"=>$src, "type"=>$type);
    }
    
    /**
     * Attach external stylesheet
     * 
     * @param string $href The relative or absolute URL to the link source.
     * @param string $type The link type. Default is text/css.
     * 
     * @return void
     * @since  1.0
     */
    public function addStyleSheet($href, $type='text/css') 
    {
        // Make source absolute URL
        $this->_makeAbsolute($href);
        
        $this->_style_sheets[] = array(
            "rel"  => "stylesheet", 
            "href" => $href, 
            "type" => $type
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
        // Add theme stylesheets
        $this->addStyleSheet($theme_url."/css/styles.css");
        
        // Start buffering
        ob_start();
        include_once $theme_path;
        // save buffer in body
        $str = ob_get_contents();
        // clean output buffer
        ob_end_clean();
        
        $this->setBody($str);
    }
    
    /**
     * Set the "body only" flag. If set to TRUE object will only include 
     * contents of the body tag. This is used for AJAX output.
     * 
     * @param bool $bool Whether or not to render as "body only".
     * 
     * @return void
     * @since  1.0
     */
    public function setBodyOnly($bool)
    {
        $this->_body_only = (bool) $bool;
    }
    
    /**
     * Make path absolute
     * 
     * @param string &$path The path we want to make absolute.
     * 
     * @return void
     * @since  1.0
     */
    private function _makeAbsolute(&$path) 
    {
        // Add the document base if a relative path
        if (substr($path, 0, 4) != 'http') {
            $uri = new PHPFrame_URI();
            $path = $uri->getBase().$path;
        }
    }
}
