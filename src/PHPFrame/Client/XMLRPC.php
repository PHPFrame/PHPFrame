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
     * @access public
     * @return PHPFrame_Client_IClient|boolean Object instance of this class if 
     *                                         correct helper for client or FALSE 
     *                                         otherwise.
     * @since  1.0
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
     * Get client name
     * 
     * @access public
     * @return string Name to identify helper type
     * @since  1.0
     */
    public function getName() 
    {
        return "xmlrpc";
    }
    
    /**    
     * Populate the Unified Request array
     * 
     * @access public
     * @return array  Unified Request Array
     * @since  1.0
     */
    public function populateRequest() 
    {
        
        global $HTTP_RAW_POST_DATA;
        
        $params = $this->_parseXMLRPC($HTTP_RAW_POST_DATA);
        return $params;
        
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the requested
     * action in the action controller. It gives the client an opportunity to do 
     * something before the component is executed.
     * 
     * @param PHPFrame_Application_Response $response The response object to prepare.
     * 
     * @access public
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Application_Response $response) {}
    
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
        
        $query = "//methodCall/methodName";
        $query_result = $domXPath->query($query);
        $methodName = $query_result->item(0)->nodeValue;
        echo $methodName."\n";
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
        
        $query = "//methodCall/params/param/value/struct/member";
        $query_result = $domXPath->query($query);
		echo "node length ".$query_result->length;  
        //if a <struct> element exists and has child nodes
        if (
        	$query_result instanceof DOMNodeList 
        	&& $query_result->length!=0 
        	&& $query_result->item(0)->hasChildNodes()
        ) {
            $members = $query_result;
//            foreach ($members as $member)
//           		var_dump($member);
           	$this->_parseXMLRPCRecurse($domXPath, $members, $array['request']);
        }
        var_dump($array);
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
 	private function _parseXMLRPCRecurse($domXPath, $nodes, &$array=array()) {
 		if (!($nodes instanceof DOMNodeList))
 			throw new PHPFrame_Exception("Invalid parameter type, nodes must be of type DOMNodeList!");
    	foreach ($nodes as $node) {
    		$query = 'name';
    		$key = $domXPath->query($query, $node)->item(0)->nodeValue;
    		$query = 'value/struct/member';
    		$value = $domXPath->query($query, $node);
    		if ($value->length!=0) {
    			$array[$key] = $this->_parseXMLRPCRecurse($domXPath, $value);
    		}
    		else {
    			$query = 'value';
    			$value = $domXPath->query($query, $node)->item(0)->firstChild;
    			if ($value->nodeName=='int')
    				$leafValue = (int)$value->nodeValue;
    			else if ($value->nodeName=='string')
    				$leafValue = (string)$value->nodeValue;
    			else if ($value->nodeName=='double')
    				$leafValue = (float) $value->nodeValue;
    			else if ($value->nodeName=='boolean')
    				$leafValue = (boolean) $value->nodeValue;
    			
    			$array[$key] = $leafValue;
    		}
        }
        
        return $array;
    }
    
    /**
     * This method is used to return the scalar value of a DOMNode. 
     * The node must be one of the scalar values as specified by the 
     * xml rpc (i4, int, boolean, string, double, dateTime.iso8601, base64).
     * 
     * @param $node DOMNode containing value to return
     * @return various int for i4, int or dateTime.iso8601 (unix timestamp) nodes, 
     * boolean for boolean, string for string or base64 and float for double
     */
    private function _nodeScalarValue($node) {
    	if (!($node instanceof DOMNode))
    		throw new PHPFrame_Exception("Invalid parameter, node must be of type DOMNode!");
    	$nodeName = $node->nodeName;
    	$time_reg = '/(^[0-9]{4})([0-9]{2})([0-9]{2})T([0-9]{2})([0-9]{2})([0-9]{2}$)/';
    	switch ($nodeName){
    		case 'i4':
    		case 'int':
    			$value = (int)$node->nodeValue;
    			break;
    		case 'boolean':
    			$value = (boolean)$node->nodeValue;
    			break;
    		case 'string':
    		case 'base64':
    			$value = (string)$node->nodeValue;
    			break;
    		case 'double':
    			$value = (float)$node->nodeValue;
    			break;
    		case 'dateTime.iso8601':
    			$matches = array();
    			preg_match($time_reg, $node->nodeValue, $matches);
    			break;
    	}
    }
}
