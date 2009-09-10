<?php
/**
 * PHPFrame/Document/RPCDocument.php
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
 * RPC Document Class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_Document
 * @since    1.0
 */
class PHPFrame_RPCDocument extends PHPFrame_XMLDocument
{
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_URI, PHPFrame_Pathway
     * @since  1.0 
     */
    public function __construct($mime="text/xml", $charset=null) 
    {
        // Call parent's constructor to set mime type
        parent::__construct($mime, $charset);
        
        // Add body
        $this->addNode($this->dom, "methodResponse");       
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
        $response = $this->dom->saveXML();
        $response = $this->indent($response);
        
        return $response;
    }
       
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_View $view        The view object to process.
     * @param bool              $apply_theme Boolean to insicate whether we want to apply 
     *                                       the overall theme or not.
     * 
     * @access public
     * @return void
     * @since  1.0
     * @todo It is very important to check path used for require_once call for security.
     */
    public function render(PHPFrame_View $view, $apply_theme=null) 
    {
        $events = PHPFrame::Session()->getSysevents()->asArray();
        
        if (
            is_array($events)
            && isset($events['summary'])
            && count($events['summary'])>1
        ){
            $summary = $events['summary'];
            if ($summary[0] == 'error')
            {
                $this->_makeFaultPayload(5,$summary[1]);
            }
            else
            {
                $view->addData('sysevents',$summary);
                $this->_makeParamPayload($view->getData());    
            }
        }
        else
        {
            $this->_makeParamPayload($view->getData());
        } 
    }
    
    /**
     * Make Fault Payload
     * 
     * This method is used to build an XML-RPC Falult Response with given faultCode and description
     * 
     * @param int $fault_code The fault code.
     * @param string $fault_string The fault description.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC methodResponse structure with Fault node.
     */
    private function _makeFaultPayload($fault_code, $fault_string)
    {
        $parent_node = $this->dom->getElementsByTagName("methodResponse")->item(0);
        $parent_node = $this->addNode($parent_node,'fault');
        $parent_node = $this->addNode($parent_node,'value');
        $fault_array = array('faultCode'=>$fault_code,'faultString'=>$fault_string);
        $parent_node = $this->_addStruct($parent_node,$fault_array);
    }
    
    /**
     * Make Param Payload
     * 
     * This method is used to build an XML-RPC Param Response from an array
     * 
     * @param mixed $param_value The parameter value as a scalar value or array.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC methodResponse structure with Param node.
     */
    private function _makeParamPayload($param_value)
    {
         $parent_node = $this->dom->getElementsByTagName("methodResponse")->item(0);
        $parent_node = $this->addNode($parent_node,'params');
        $parent_node = $this->addNode($parent_node,'param');
        $this->_buildNode($parent_node,'value',$param_value);
    }
    
    /**
     * Build Node
     * 
     * This method builds a DOMNode Tree|Leaf from the node Parent, Name and Value
     * 
     * @param DOMNode $parentNode The name to be given to the node.
     * @param string $nodeName The name to be given to the node.
     * @param mixed $nodeValue The node value (string|int|double|array).
     * 
     * @access private
     * @since  1.0
     */
    private function _buildNode($parent_node, $node_name, $node_value)
    {
        if (!empty($node_value))
        {
            $parent_node = $this->addNode($parent_node, $node_name);
            
            if (
                $node_value instanceof PHPFrame_User 
                || $node_value instanceof PHPFrame_DatabaseRowCollection
                || $node_value instanceof PHPFrame_DatabaseRow 
            ){
                $node_value = $node_value->toArray();
            }
            
            if (is_array($node_value))
            { 
                if ($this->_isAssoc($node_value)){
                    $this->_addStruct($parent_node, $node_value);
                }
                else{
                    $this->_addArray($parent_node, $node_value);;
                }
            }
            else
            { 
                if ($parent_node->nodeName == 'value') 
                {
                    $parent_node = $this->_addType($parent_node, $node_value);    
                }
                $this->addNodeContent($parent_node,$node_value);
            }
        }
        else
        {
            if ($node_name == 'value') 
            {
                $parent_node = $this->addNode($parent_node, 'value');
                $this->addNodeContent($parent_node,'NULL');    
            }
        }
    }
       
    /**
     * Is Assoc
     * 
     * This method is used to check if an array is an associative array
     * 
     * @param array $testArray The array to be tested.
     * 
     * @access private
     * @since  1.0
     * @return boolean True if array is associative or empty.
     */
    private function _isAssoc($test_array)
    {
        $assoc = false;
        if (empty($test_array)) return true;
        $indexes = array_keys($test_array);
        $counter = 0;
        foreach($indexes as $index)
        {
            if ($counter !== $index)
            {
                $assoc = true;
            }
            $counter++;
        }
        unset($index);
        return $assoc;
    }
    
    /**
     * Make Type
     * 
     * This method is used to wrap node values in tags denoting their type
     * 
     * @param mixed $nodeValue The value to be checked and wrapped if applicable.
     * 
     * @access private
     * @since  1.0
     * @return DOMNode The original node, or firstChild if type added.
     */
    private function _addType($parent_node, $node_value)
    {
        $type = 'string';
        if (is_int($node_value)) $type = 'int';
        if (is_bool($node_value)) $type = 'boolean';
        if (is_float($node_value)) $type = 'double';
        if (is_double($node_value)) $type = 'double';
        
        $parent_node = $this->addNode($parent_node, $type);
        
        return $parent_node;
    }
    
    /**
     * Make Struct
     * 
     * This method is used to build an XML-RPC <struct> structure from an assoc_array
     * 
     * @param array $assocArray The associative array.
     * 
     * @access private
     * @since  1.0
     */
    private function _addStruct($parent_node, $assoc_array)
    {
        $parent_node = $this->addNode($parent_node,'struct');
        
        foreach($assoc_array as $key => $value)
        {
            $local_parent = $this->addNode($parent_node,'member');
            $this->addNode($local_parent, 'name', null, $key);
            $this->_buildNode($local_parent, 'value', $value);
        }
    }
    
    /**
     * Make Array
     * 
     * This method is used to build an XML-RPC <array> structure from an indexed array
     * 
     * @param array $indexArray The array.
     * 
     * @access private
     * @since  1.0
     */
    private function _addArray($parent_node, $index_array)
    {
        $parent_node = $this->addNode($parent_node,'array');
        $parent_node = $this->addNode($parent_node,'data');
        foreach($index_array as $value)
        {
            $this->_buildNode($parent_node, 'value', $value);
        }
    }
}