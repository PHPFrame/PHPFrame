<?php
/**
 * PHPFrame/Document/HTML.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * HTML Document Class
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Document
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see        PHPFrame_Document
 * @since      1.0
 */
class PHPFrame_Document_HTML extends PHPFrame_Document
{
    /**
     * The qualified name of the document type to create. 
     * 
     * @var string
     */
    private $_qualified_name="html";
    /**
     * DOM Document Type object
     * 
     * @var DOMDocumentType
     */
    private $_doctype=null;
    /**
     * DOM Document object
     * 
     * @var DOMDocument
     */
    private $_dom=null;
    /**
     * The document body
     * 
     * @var string
     */
    private $_body=null;
    
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_Utils_URI, PHPFrame_Application_Pathway
     * @since  1.0 
     */
    public function __construct() 
    {
        // Call parent's constructor to set mime type
        parent::__construct('text/html');
        
        // Acquire DOM object of HTML type
        $this->_dom = DOMImplementation::createDocument(null, 
                                                        $this->_qualified_name, 
                                                        $this->getDocType()); 
        
        // Get root node
        $html_node = $this->_dom->getElementsByTagName("html")->item(0);
        
        // Add head
        $head_node = $this->_addNode($html_node, "head");
        // Add body
        $this->_addNode($html_node, "body", null, "{content}");
        
        // Add meta tags
        $this->addMetaTag("generator", "PHPFrame");
        $this->addMetaTag(null, $this->_mime_type."; charset=".$this->_charset, "Content-Type");
        
        // Add base url
        $uri = new PHPFrame_Utils_URI();
        $this->_addNode($head_node, "base", array("href"=>$uri->getBase()));
        
        // Acquire a pathway object used by this document
        $this->_pathway = new PHPFrame_Application_Pathway();
    }
    
    public function setBody($str)
    {
        $this->_body = (string) $str;
    }
    
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_MVC_View $view The view object to process.
     * 
     * @access public
     * @return void
     * @since  1.0
     * @todo It is very important to check path used for require_once call for security.
     */
    public function renderView(PHPFrame_MVC_View $view) 
    {
        $tmpl_path = COMPONENT_PATH.DS."views";
        $tmpl_path .= DS.$view->getName().DS."tmpl";
        
        // Add client specific template to path
        $tmpl_path .= DS.PHPFrame::Session()->getClientName();
        
        $layout = $view->getLayout();
        if ($layout) {
            $tmpl_path .= "_".$layout;
        }
        
        $tmpl_path .= ".php";
        
        if (is_file($tmpl_path)) {
            // Start buffering
            ob_start();
            // set view data as local array
            $data = $view->getData();
            // Include template file
            require_once $tmpl_path;
            // save buffer in body property
            $this->_body = ob_get_contents();
            // clean output buffer
            ob_end_clean();
        } else {
            throw new PHPFrame_Exception("Layout template file ".$tmpl_path." not found.");
        }
        
        $this->_applyTheme($view);
    }
    
    /**
     * Convert object to string
     * 
     * @access public
     * @return string
     * @since  1.0
     */
    public function toString()
    {
        // Add title tag in head node 
        $head_node = $this->_dom->getElementsByTagName("head")->item(0);
        $this->_addNode($head_node, "title", null, $this->getTitle());
        
        // Render DOM Document as HTML string
        $html = $this->_dom->saveHTML();
        
        // Add body
        $html = str_replace("{content}", $this->_body, $html);
        
        return $html;
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
        if (!($this->_doctype instanceof DOMDocumentType)) {
             // Create doc type object
            $publicId = "-//W3C//DTD HTML 4.01//EN";
            $systemId = "http://www.w3.org/TR/html4/strict.dtd";
            $this->_doctype = DOMImplementation::createDocumentType($this->_qualified_name, 
                                                                    $publicId, 
                                                                    $systemId);
        }
        
        return $this->_doctype;
    }
    
    /**
     * Add meta tag
     * 
     * @param string $name       This attribute identifies a property name. This 
     *                           specification does not list legal values for this 
     *                           attribute.
     * @param string $content    This attribute specifies a property's value. This 
     *                           specification does not list legal values for this 
     *                           attribute.
     * @param string $http_equiv This attribute may be used in place of the name attribute. 
     *                           HTTP servers use this attribute to gather information for 
     *                           HTTP response message headers.
     * 
     * @return void
     * @since  1.0
     * @todo   This method should check whether the meta tag has already been 
     *         added to avoid printing the same meta tag twice.
     */
    function addMetaTag($name, $content, $http_equiv=null) 
    {
        // Get head node
        $head_node = $this->_dom->getElementsByTagName("head")->item(0);
        
        // Creare meta tag node
        $meta_node = $this->_addNode($head_node, "meta");
        
        // Add name attribute if any
        if (!is_null($name)) {
            $this->_addNodeAttr($meta_node, "name", $name);
        }
        // Add http_equiv attribute if any
        if (!is_null($http_equiv)) {
            $this->_addNodeAttr($meta_node, "http_equiv", $http_equiv);
        }
        // Add content attribute
        $this->_addNodeAttr($meta_node, "content", $content);
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
    function addScript($src, $type='text/javascript') 
    {
        // Make source absolute URL
        $this->_makeAbsolute($src);
        
        // Get head node
        $head_node = $this->_dom->getElementsByTagName("head")->item(0);
        
        // Create script tag node
        $this->_addNode($head_node, "script", array("type"=>$type, "src"=>$src));
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
        
        // Get head node
        $head_node = $this->_dom->getElementsByTagName("head")->item(0);
        
        // Create script tag node
        $attrs = array("rel"=>"stylesheet", "href"=>$href, "type"=>$type);
        $this->_addNode($head_node, "link", $attrs);
    }
    
    /**
     * Add node/tag
     * 
     * @param DOMNode $parent  The parent object to which we want to add the new node.
     * @param string  $name    The name of the new node or tag
     * @param array   $attrs   An assoc array containing attributes key/value pairs.
     * @param string  $content Text content of the node if any
     * 
     * @access private
     * @return DOMNode Returns a reference to the newly created node
     * @since  1.0
     */
    private function _addNode($parent, $name, $attrs=array(), $content=null)
    {
        $new_node = $this->_dom->createElement($name);
        $parent->appendChild($new_node);
        
        // Add attributes if any
        if (is_array($attrs) && count($attrs) > 0) {
            foreach ($attrs as $key=>$value) {
                $this->_addNodeAttr($new_node, $key, $value);
            }
        }
        
        // Add text content if any
        if (!is_null($content)) {
            $this->_addNodeContent($new_node, $content);
        }
        
        return $new_node;
    }
    
    /**
     * Add an attribute to a given node
     * 
     * @param DOMNode $node       The node we want to add the attributes to.
     * @param string  $attr_name  The attribute name
     * @param string  $attr_value The value for the attribute if any.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _addNodeAttr($node, $attr_name, $attr_value)
    {
        // Create attribute
        $attr = $this->_dom->createAttribute($attr_name);
        
        // Add attribute value
        $value = $this->_dom->createTextNode($attr_value);
        $attr->appendChild($value);
        
        // Append attribute to node
        $node->appendChild($attr);
    }
    
    /**
     * Add content to given node
     * 
     * @param DOMNode $node The node where to add the content text.
     * @param string  $str  The text to add to the node
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _addNodeContent($node, $str)
    {
        $text_node = $this->_dom->createTextNode($str);
        $node->appendChild($text_node);
    }
    
    /**
     * Apply theme
     * 
     * @param PHPFrame_MVC_View $view The view object to process.
     * 
     * @access private
     * @return void
     * @since  1.0
     */
    private function _applyTheme(PHPFrame_MVC_View $view) 
    {
        // Make widgets available to templates
        $modules = PHPFrame::AppRegistry()->getModules();
        
        // If tmpl flag is set to component in request it means that
        // we dont need to wrap the component output in the overall template
        // so we just prepend the sytem events and return
        if (PHPFrame::Request()->get('tmpl') == 'component') {
            $sys_events = $modules->display('sysevents', '_sysevents');
            $this->_body = $sys_events.$this->_body;
            return;
        }
        
        // Add theme stylesheets
        $this->addStyleSheet("themes/".config::THEME."/css/styles.css");
        
        // make pathway available in local scope
        $pathway = $view->getPathway();
        
        $component_output = $this->_body;
        
        // Set file name to load depending on session auth
        $session = PHPFrame::Session();
        if (!$session->isAuth()) {
            $template_filename = 'login.php';
        }
        else {
            $template_filename = 'index.php';
        }
        
        $template_path = _ABS_PATH.DS."public".DS."themes".DS.config::THEME;
        
        // Start buffering
        ob_start();
        require_once $template_path.DS.$template_filename;
        // save buffer in body
        $this->_body = ob_get_contents();
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
            $uri = new PHPFrame_Utils_URI();
            $path = $uri->getBase().$path;
        }
    }
}
