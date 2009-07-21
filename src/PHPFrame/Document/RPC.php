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
    public function render(PHPFrame_MVC_View $view) 
    {
    	PHPFrame_Debug_Logger::write('RENDERING');
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
		$this->addNode($parentNode,'fault');
		$parentNode = $parentNode->lastChild;
		$this->addNode($parentNode,'value');
		$parentNode = $parentNode->lastChild;
		$this->addNode($parentNode,'struct');
		$parentNode = $parentNode->lastChild;
		$this->addNode($parentNode,'member');
		$this->addNode($parentNode->lastChild,'faultCode', null, $faultCode);
		$this->addNode($parentNode,'member');
		$this->addNode($parentNode->lastChild,'faultString', null, $faultString);
    }
    
	/**
     * Make Param Payload
     * 
     * This method is used to build an XML-RPC Param Responsewith given parameter Value
     * 
     * @param $paramValue The parameter value as an associative array.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC methodResponse structure with Param node.
     */
    private function _makeParamPayload($paramValue)
    {
		$parentNode = $this->dom->getElementsByTagName("methodResponse")->item(0);
		$this->addNode($parentNode,'params');
		$parentNode = $parentNode->lastChild;
		$this->addNode($parentNode,'param');
		$parentNode = $parentNode->lastChild;
		$this->addNode($parentNode,'value');
		$parentNode = $parentNode->lastChild;
		$this->_makeNode($parentNode, $paramValue);
    }
    
    /**
     * Make Node
     * 
     * This method is used to build DOMNodes given the node Name and Value
     * 
     * @param $nodeName The name to be given to the node.
     * @param $nodeValue The textValue to be given to the node.
     * 
     * @access private
     * @since  1.0
     * @return string DOMNode with given name and value.
     */
    private function _makeNode($parentNode, $nodeValue)
    {
    	//TODO: Check for NULL and types other than basic scalar / assoc_array & toString()?
    	if (!empty($nodeValue))
    	{
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
				$this->_addType($parentNode, $nodeValue);
				$this->addNodeContent($parentNode,$nodeValue);
	    	}
    	}
    }
    
	/**
     * Is Assoc
     * 
     * This method is used to check if an array is an associative array
     * 
     * @param $testArray The array to be tested.
     * 
     * @access private
     * @since  1.0
     * @return boolean True if array is associative or empty.
     */
	private function _isAssoc($testArray)
	{
		$indexed = true;
		if (empty($testArray)) return true;
		$indexes = array_keys($testArray);
		$counter = 0;
		foreach($indexes as $index)
		{
			if ($counter !== $index)
			{
				$indexed = false;
			}
			$counter++;
		}
		unset($index);
		return !$indexed;
	}
    
	/**
     * Make Type
     * 
     * This method is used to wrap node values in tags denoting their type
     * 
     * @param $nodeValue The value to be checked and wrapped if applicable.
     * 
     * @access private
     * @since  1.0
     * @return string nodeValue wrapped in nodeType tags (if applicable).
     */
    private function _addType(&$parentNode, $nodeValue)
    {
    	$type = 'string';
    	if (is_int($nodeValue)) $type = 'int';
    	if (is_bool($nodeValue)) $type = 'boolean';
    	if (is_float($nodeValue)) $type = 'double';
    	if (is_double($nodeValue)) $type = 'double';
    	if($type!='string')
    	{	
    		$this->addNode($parentNode, $type);
    		$parentNode = $parentNode->getElementsByTagName($type);
    	}
    }
    
	/**
     * Make Struct
     * 
     * This method is used to build an XML-RPC <struct> structure from an assoc_array
     * 
     * @param $assocArray The associative array.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC <struct> structure
     */
    private function _addStruct($parentNode, $assocArray)
    {
    	$this->addNode($parentNode,'struct');
		$parentNode = $parentNode->lastChild;
    	foreach($assocArray as $key => $value)
    	{
			$this->addNode($parentNode,'member');
			$localParent = $parentNode->lastChild;
			$this->addNode($localParent, 'name', null, $key);
			$this->addNode($localParent, 'value');
			$this->_makeNode($localParent->lastChild, $value);
    	}
    }
    
	/**
     * Make Array
     * 
     * This method is used to build an XML-RPC <array> structure from an indexed array
     * 
     * @param $indexArray The array.
     * 
     * @access private
     * @since  1.0
     * @return string An XML-RPC <array> structure
     */
    private function _addArray($parentNode, $indexArray)
    {
    	$this->addNode($parentNode,'array');
    	$this->addNode($parentNode->firstChild,'data');
		$parentNode = $parentNode->firstChild->firstChild;
    	foreach($indexArray as $value)
    	{
    		$this->addNode($parentNode,'value');
    		$this->_makenode($parentNode->lastChild, $value);
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
        return $response;
    }
}