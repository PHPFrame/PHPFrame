<?php
/**
 * PHPFrame/Document/RPC.php
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
 * RPC Document Class
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
class PHPFrame_Document_RPC extends PHPFrame_Document_XML
{
    /**
     * Constructor
     * 
     * @access public
     * @return void
     * @uses   DOMImplementation, PHPFrame_Utils_URI, PHPFrame_Application_Pathway
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
     * Render view and store in document's body
     * 
     * This method is invoked by the views and renders the ouput data in the
     * document specific format.
     * 
     * @param PHPFrame_MVC_View $view        The view object to process.
     * @param bool              $apply_theme Boolean to insicate whether we want to apply 
     *                                       the overall theme or not.
     * 
     * @access public
     * @return void
     * @since  1.0
     * @todo It is very important to check path used for require_once call for security.
     */
    public function render(PHPFrame_MVC_View $view, $apply_theme=null) 
    {
		$this->_makeParamPayload($view->getData());
    }
    
	/**
     * Make Fault Payload
     * 
     * This method is used to build an XML-RPC Falult Response with given faultCode and description
     * 
     * @param $faultCode The fault code.
     * @param $faultString The fault description.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC methodResponse structure with Fault node.
     */
    private function _makeFaultPayload($faultCode, $faultString)
    {
    	$parentNode = $this->dom->getElementsByTagName("methodResponse")->item(0);
		$parentNode = $this->addNode($parentNode,'fault');
		$parentNode = $this->addNode($parentNode,'value');
		$parentNode = $this->addNode($parentNode,'struct');
		$this->addNode($parentNode,'member');
		$this->addNode($parentNode->lastChild,'faultCode', null, $faultCode);
		$this->addNode($parentNode,'member');
		$this->addNode($parentNode->lastChild,'faultString', null, $faultString);
    }
    
	/**
     * Make Param Payload
     * 
     * This method is used to build an XML-RPC Param Response from an array
     * 
     * @param mixed $paramValue The parameter value as a scalar value or array.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC methodResponse structure with Param node.
     */
    private function _makeParamPayload($paramValue)
    {
 		$parentNode = $this->dom->getElementsByTagName("methodResponse")->item(0);
		$parentNode = $this->addNode($parentNode,'params');
		$parentNode = $this->addNode($parentNode,'param');
		$this->_buildNode($parentNode,'value',$paramValue);
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
    private function _buildNode($parentNode, $nodeName, $nodeValue)
    {
    	if (!empty($nodeValue))
    	{
    		$parentNode = $this->addNode($parentNode, $nodeName);
    		
	    	if (is_array($nodeValue))
	    	{ 
	    		if ($this->_isAssoc($nodeValue)){
					$this->_addStruct($parentNode, $nodeValue);
	    		}
				else{
					$this->_addArray($parentNode, $nodeValue);;
				}
	    	}
	    	else
	    	{ 
				$parentNode = $this->_addType($parentNode, $nodeValue);
				$this->addNodeContent($parentNode,$nodeValue);
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
	private function _isAssoc($testArray)
	{
		$assoc = false;
		if (empty($testArray)) return true;
		$indexes = array_keys($testArray);
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
    private function _addType($parentNode, $nodeValue)
    {
    	$type = 'string';
    	if (is_int($nodeValue)) $type = 'int';
    	if (is_bool($nodeValue)) $type = 'boolean';
    	if (is_float($nodeValue)) $type = 'double';
    	if (is_double($nodeValue)) $type = 'double';
    	if($type!='string')
    	{	
    		$parentNode = $this->addNode($parentNode, $type);
    	}
    	
    	return $parentNode;
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
    private function _addStruct($parentNode, $assocArray)
    {
    	$parentNode = $this->addNode($parentNode,'struct');
    	
    	foreach($assocArray as $key => $value)
    	{
			$localParent = $this->addNode($parentNode,'member');
			$this->addNode($localParent, 'name', null, $key);
			$this->_buildNode($localParent, 'value', $value);
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
    private function _addArray($parentNode, $indexArray)
    {
    	$parentNode = $this->addNode($parentNode,'array');
    	$parentNode = $this->addNode($parentNode,'data');
    	foreach($indexArray as $value)
    	{
    		$this->_buildNode($parentNode, 'value', $value);
    	}
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
        $response = $this->dom->saveXML();
        return str_replace('<param/>','<param><value></value></param>',$response);
    }
}