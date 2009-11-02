<?php
/**
 * PHPFrame/Document/RPCRenderer.php
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
 * XML-RPC renderer class
 * 
 * @category PHPFrame
 * @package  Document
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://code.google.com/p/phpframe/source/browse/#svn/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_RPCRenderer implements PHPFrame_IRenderer
{
    public function render($value)
    {
        if ($value instanceof PHPFrame_View) {
            $value = $this->renderView($value);
        }
        
        return strip_tags(trim((string) $value));
    }
    /**
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_View $view        The view object to process.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function renderView(PHPFrame_View $view) 
    {
        $sysevents = PHPFrame::Session()->getSysevents();
        
        if (count($sysevents)) {
            $array           = iterator_to_array($sysevents);
            $last_event_msg  = $array[0][0];
            $last_event_type = $array[0][1];
            
            if ($last_event_type == PHPFrame_Subject::EVENT_TYPE_ERROR) {
                $this->_makeFaultPayload(5, $last_event_msg);
            } else {
                $view->addData('sysevents', $last_event_msg);
                $this->_makeParamPayload($view->getData());    
            }
        } else {
            $this->_makeParamPayload($view->getData());
        }
    }
    
    /**
     * Make Fault Payload
     * 
     * This method is used to build an XML-RPC Falult Response with given 
     * faultCode and description
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
        $doc = PHPFrame::Response()->getDocument();
        
        $parent_node = $doc->getDom()->getElementsByTagName("methodResponse")->item(0);
        $parent_node = $doc->addNode($parent_node, 'fault');
        $parent_node = $doc->addNode($parent_node, 'value');
        $fault_array = array('faultCode'=>$fault_code, 'faultString'=>$fault_string);
        $parent_node = $this->_addStruct($parent_node, $fault_array);
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
        $doc = PHPFrame::Response()->getDocument();
        
        $parent_node = $doc->getDom()->getElementsByTagName("methodResponse")->item(0);
        $parent_node = $doc->addNode($parent_node, 'params');
        $parent_node = $doc->addNode($parent_node, 'param');
        
        $this->_buildNode($parent_node, 'value', $param_value);
    }
    
    /**
     * Build Node
     * 
     * This method builds a DOMNode Tree|Leaf from the node Parent, Name and 
     * Value
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
        $doc = PHPFrame::Response()->getDocument();
        
        if (!empty($node_value)) {
            $parent_node = $doc->addNode($parent_node, $node_name);
            
            if (
                $node_value instanceof PHPFrame_User 
                || $node_value instanceof PHPFrame_Collection
                || $node_value instanceof PHPFrame_PersistentObject 
                || $node_value instanceof SPLObjectStorage
            ) {
                $node_value = iterator_to_array($node_value);
            }
            
            if (is_array($node_value)) { 
                if ($this->_isAssoc($node_value)){
                    $this->_addStruct($parent_node, $node_value);
                } else {
                    $this->_addArray($parent_node, $node_value);;
                }
            } else { 
                if ($parent_node->nodeName == 'value') {
                    $parent_node = $this->_addType($parent_node, $node_value);    
                }
                $doc->addNodeContent($parent_node, $node_value);
            }
        } else {
            if ($node_name == 'value') {
                $parent_node = $doc->addNode($parent_node, 'value');
                $doc->addNodeContent($parent_node, 'NULL');    
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
        foreach($indexes as $index) {
            if ($counter !== $index) {
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
        $doc = PHPFrame::Response()->getDocument();
        
        $type = 'string';
        if (is_int($node_value))    $type = 'int';
        if (is_bool($node_value))   $type = 'boolean';
        if (is_float($node_value))  $type = 'double';
        if (is_double($node_value)) $type = 'double';
        
        $parent_node = $doc->addNode($parent_node, $type);
        
        return $parent_node;
    }
    
    /**
     * Make Struct
     * 
     * This method is used to build an XML-RPC <struct> structure from an 
     * assoc_array
     * 
     * @param array $assocArray The associative array.
     * 
     * @access private
     * @since  1.0
     */
    private function _addStruct($parent_node, $assoc_array)
    {
        $doc = PHPFrame::Response()->getDocument();
        
        $parent_node = $doc->addNode($parent_node,'struct');
        
        foreach($assoc_array as $key=>$value) {
            $local_parent = $doc->addNode($parent_node, 'member');
            $doc->addNode($local_parent, 'name', null, $key);
            $this->_buildNode($local_parent, 'value', $value);
        }
    }
    
    /**
     * Make Array
     * 
     * This method is used to build an XML-RPC <array> structure from an indexed 
     * array
     * 
     * @param array $indexArray The array.
     * 
     * @access private
     * @since  1.0
     */
    private function _addArray($parent_node, $index_array)
    {
        $doc = PHPFrame::Response()->getDocument();
        
        $parent_node = $doc->addNode($parent_node, 'array');
        $parent_node = $doc->addNode($parent_node, 'data');
        
        foreach ($index_array as $value) {
            $this->_buildNode($parent_node, 'value', $value);
        }
    }
}
