<?php
/**
 * PHPFrame/Client/XMLRPC.php
 * 
 * PHP version 5
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @copyright  2009 E-noise.com Limited
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 */

/**
 * Client for Mobile Devices
 * 
 * @category   MVC_Framework
 * @package    PHPFrame
 * @subpackage Client
 * @author     Luis Montero <luis.montero@e-noise.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @since      1.0
 */
class PHPFrame_Client_XMLRPC implements PHPFrame_Client_IClient
{
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @static
     * @access    public
     * @return    PHPFrame_Client_IClient|boolean    Object instance of this class if correct helper for client or false otherwise.
     */
    public static function detect() 
    {
        
        global $HTTP_RAW_POST_DATA;
            
        //check existance of $_HTTP_RAW_POST_DATA array
        if (count($HTTP_RAW_POST_DATA) > 0) {
            //check for a valid XML structure
            $domDocument = new DOMDocument;
            if ($domDocument->loadXML($HTTP_RAW_POST_DATA))
            {
                $domXPath = new DOMXPath($domDocument);
                //check for valid RPC
                //query always returns DOMNodelist,
                //item(0) returns DOMNode or null,
                //DOMNode has $nodeValue and null->nodeValue = null
                //TODO: xmlrpc detect perhaps check for empty methodName string?
                if ($domXPath->query("//methodCall/methodName")->item(0)->nodeValue != null)
                {
                    return new self;
                }
            }
        }
        return false;
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access    public
     * @return    array    Unified Request Array
     */
    public function populateURA() 
    {
        
        global $HTTP_RAW_POST_DATA;
        
        $params = $this->_parseXMLRPC($HTTP_RAW_POST_DATA);
        return $params;
        
    }
    
    /**    
     * Get helper name
     * 
     * @access    public
     * @return    string    Name to identify helper type
     */
    public function getName() 
    {
        return "xmlrpc";
    }
    
    /**
     * Pre action hook
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @return    void
     */
    public function preActionHook() {}
    
    /**
     * Render component view
     * 
     * This method is invoked by the views and renders the ouput data in the format specified
     * by the client.
     * 
     * @param    array    $data    An array containing the data assigned to the view.
     * @return    void
     */
    public function renderView($data) {}
    
    /**
     * Render overall template
     *
     * @param    string    &$str    A string containing the component output.
     * @return    void
     */
    public function renderTemplate(&$str) {}
    
    /**
     * This method is used to parse an XML remote procedure call
     *
     * @access private
     * @param string $xml A string containing the XML call.
     * @return array A nice asociative array with all the data.
     */
    private function _parseXMLRPC($xml) 
    {
        
        $array = array();
        
        $domDocument = new DOMDocument;
        $domDocument->loadXML($xml);
        $domXPath = new DOMXPath($domDocument);
        
        $methodName = $domXPath->query("//methodCall/methodName")->item(0)->nodeValue;
        
        //look for 'component.action' format 
        preg_match('/^([a-zA-Z]+)(\.([a-zA-Z_]+))?$/', $methodName, $matches);
        
        //matches?
        if (count($matches) > 0) {
            //first match is component
            $array['request']['component'] = 'com_'.$matches[1];
            //third match is action (if it exists) 
            if (count($matches) > 2) {
                $array['request']['action'] = $matches[3];    
            }
        } 
        
        //if a <struct> element exists and has child nodes
        if ($domXPath->query("//methodCall/params/param/value/struct")->item(0)->hasChildNodes()) {
            
            $query = "//methodCall/params/param/value/struct/member";
            $members = $domXPath->query($query);

            $array['request'] = $this->_parseXMLRPCRecurse($domXPath, $members);
            
        }
        return $array;
    }
       
    /**
     * This method is used by _parseXMLResponse() to loop recursively through XML nodes 
     * and collect data.
     *
     * @access private
     * @param object $domXPath    The DOMXPath object used for parsing the XML. This object 
     *                             is created in _parseXMLResponse().
     * @param object $nodes
     * @param string $search_path
     * @param array $array
     * @return array
     */
    private function _parseXMLRPCRecurse(&$domXPath, $nodes, $search_path="", &$array=array()) 
    {
        
        foreach ($nodes as $node) {
             
ob_start();
var_dump($nodes->item(2)->childNodes->item(2)->firstChild->nodeName);
$output = ob_get_contents();
ob_end_clean();
file_put_contents("/var/www/extranetoffice/test/xmlrpc/globals.html",$output);
exit;        

            if ($node->childNodes->item(2)->hasChildNodes()) {
                //recurse    
            }
            else {
                $array[$node->childNodes->item(0)->textContent] = $node->childNodes->item(2)->textContent;
            }
            
            /*if ($node->childNodes->length > 1) {
                $query = "params/param";
                $children = $domXPath->query($query, $node);
                $this->_parseXMLResponseRecurse($domXPath, $children, $query, $array[$node->getAttribute("key")]);
                      
                $query = "struct/member";
                $children = $domXPath->query($query, $node);
                $this->_parseXMLResponseRecurse($domXPath, $children, $query, $array[$node->getAttribute("key")]);
            }
            else {
                       $array[$node->getAttribute("key")] = $node->nodeValue;
            }*/
        }
        
        return $array;
    }
}
