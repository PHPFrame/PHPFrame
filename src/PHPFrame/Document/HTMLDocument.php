<?php
/**
 * PHPFrame/Document/HTMLDocument.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Document
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 E-noise.com Limited
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version   SVN: $Id$
 * @link      http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * HTML Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_HTMLDocument extends PHPFrame_XMLDocument
{
    private $_meta_tags = array();
    private $_scripts = array();
    private $_style_sheets = array();
    
    /**
     * Constructor
     * 
     * @param string $mime    [Optional]
     * @param string $charset [Optional]
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_URI, PHPFrame_Pathway
     * @since  1.0 
     */
    public function __construct($mime="text/html", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Acquire DOM object of HTML type
        $imp = new DOMImplementation;
        $this->dom = $imp->createDocument(
            null,
            "html",
            $this->getDocType()
        ); 
        
        // Get root node
        $html_node = $this->dom->getElementsByTagName("html")->item(0);
        
        // Add head
        $head_node = $this->addNode($html_node, "head");
        // Add body
        $this->addNode($html_node, "body", null, "{content}");
        
        // Add meta tags
        $this->addMetaTag("generator", "PHPFrame");
        $this->addMetaTag(
            null, 
            $this->getMimeType()."; charset=".$this->getCharset(),
            "Content-Type"
        );
        
        // Add base url
        $uri = new PHPFrame_URI();
        $this->addNode($head_node, "base", array("href"=>$uri->getBase()));
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function __toString()
    {
        // Add title tag in head node 
        $head_node = $this->dom->getElementsByTagName("head")->item(0);
        $this->addNode($head_node, "title", null, $this->getTitle());
        
        // Add meta tags
        foreach ($this->_meta_tags as $meta_tag) {
            $meta_node = $this->addNode($head_node, "meta");
            
            // Add name attribute if any
            if (!is_null($meta_tag["name"])) {
                $this->addNodeAttr($meta_node, "name", $meta_tag["name"]);
            }
            // Add http_equiv attribute if any
            if (!is_null($meta_tag["http_equiv"])) {
                $this->addNodeAttr($meta_node, "http_equiv", $meta_tag["http_equiv"]);
            }
            
            // Add content attribute
            $this->addNodeAttr($meta_node, "content", $meta_tag["content"]);
        }
        
        // Add scripts
        foreach ($this->_scripts as $script_attr) {
            // Create script tag node
            $this->addNode($head_node, "script", $script_attr);
        }
        
        // Add styles
        foreach ($this->_style_sheets as $style_sheet_attr) {
            $this->addNode($head_node, "link", $style_sheet_attr);
        }
        
        // Render DOM Document as HTML string
        $html = $this->indent($this->dom->saveHTML());
        
        // Add body and return
        return str_replace("{content}", $this->getBody(), $html);
    }
    
    /**
     * Get DOM Document Type object
     * 
     * @access public
     * @return DOMDocumentType
     * @since  1.0
     */
    public function getDocType()
    {
        // Create new doc type object if we don't have one yet
        if (!($this->doctype instanceof DOMDocumentType)) {
             // Create doc type object
            $publicId = "-//W3C//DTD HTML 4.01//EN";
            $systemId = "http://www.w3.org/TR/html4/strict.dtd";
            $imp = new DOMImplementation;
            $this->doctype = $imp->createDocumentType(
                "html",
                $publicId,
                $systemId
            );
        }
        
        return $this->doctype;
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
     * @access public
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
     * @access public
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
     * @access public
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
     * Set the document body (overrides inherited method)
     * 
     * @param string $str
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function setBody($str, $apply_theme=true)
    {
        parent::setBody($str);
        
        if (
            $apply_theme 
            && PHPFrame::getRunLevel() > 1 
            && !PHPFrame::Request()->isAJAX()
        ) {
            $this->_applyTheme();
        }
    }
    
    /**
     * Apply theme
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _applyTheme() 
    {
        // Add theme stylesheets
        $this->addStyleSheet("themes/".PHPFrame::Config()->get("theme")."/css/styles.css");
        
        // make pathway available in local scope
        $pathway = PHPFrame::Response()->getPathway();
        
        $component_output = $this->getBody();
        
        // Set file name to load depending on session auth
        $controller = PHPFrame::Request()->getControllerName();
        if ($controller == "login") {
            $template_filename = 'login.php';
        } else {
            $template_filename = 'index.php';
        }
        
        $template_path = PHPFRAME_INSTALL_DIR.DS."public".DS."themes".DS.PHPFrame::Config()->get("theme");
        
        // Start buffering
        ob_start();
        require_once $template_path.DS.$template_filename;
        // save buffer in body
        parent::setBody(ob_get_contents());
        // clean output buffer
        ob_end_clean();
    }
    
    /**
     * Make path absolute
     * 
     * @param string $path The path we want to make absolute.
     * 
     * @access private
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
