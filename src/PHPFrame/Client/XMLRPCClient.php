<?php
/**
 * PHPFrame/Client/XMLRPCClient.php
 * 
 * PHP version 5
 * 
 * @category  PHPFrame
 * @package   Client
 * @author    Luis Montero <luis.montero@e-noise.com>
 * @copyright 2009 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 * @ignore
 */

/**
 * XMLRPC Client implementation
 * 
 * @category PHPFrame
 * @package  Client
 * @author   Luis Montero <luis.montero@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 * @ignore
 */
class PHPFrame_XMLRPCClient extends PHPFrame_Client
{
    
    /**
     * Check if this is the correct helper for the client being used
     * 
     * @return PHPFrame_Client|boolean Object instance of this class if correct 
     *                                 helper for client or FALSE otherwise.
     * @since  1.0
     */
    public static function detect() 
    {
        global $HTTP_RAW_POST_DATA;
        
        //check existance of $_HTTP_RAW_POST_DATA array
        if (count($HTTP_RAW_POST_DATA) > 0) {
            //check for a valid XML structure
            $domDocument = new DOMDocument;
            if ($domDocument->loadXML($HTTP_RAW_POST_DATA)) {
                $domXPath = new DOMXPath($domDocument);
                //check for valid RPC
                $query       = "//methodCall/methodName";
                $method_node = $domXPath->query($query)->item(0);
                if ($method_node->nodeValue != null) {
                    return new self;
                }
            } else {
                throw new RuntimeException("Given xml is invalid!");
            }
        }
        
        return false;
    }
    
    /**    
     * Get client name
     * 
     * @return string Name to identify client type.
     * @since  1.0
     */
    public function getName() 
    {
        return "xmlrpc";
    }
    
    /**    
     * Populate the Request object.
     * 
     * @param PHPFrame_Request $request Reference to the request object.
     * 
     * @return void
     * @since  1.0
     */
    public function populateRequest(PHPFrame_Request $request) 
    {
        global $HTTP_RAW_POST_DATA;
        
        $this->_parseXMLRPC($HTTP_RAW_POST_DATA, $request);
        
        $request->setRequestTime(time());
        $request->setQuiet(false);
        
        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace('_', ' ', substr($key, 5));
                $key = str_replace(' ', '-', ucwords(strtolower($key)));
                $request->setHeader($key, $value);
            } elseif ($key == "REQUEST_METHOD") {
                $request->setMethod($value);
            } elseif ($key == "REMOTE_ADDR") {
                $request->setRemoteAddr($value);
            } elseif ($key == "REQUEST_URI") {
                $request->setRequestURI($value);
            } elseif ($key == "SCRIPT_NAME") {
                $request->setScriptName($value);
            } elseif ($key == "QUERY_STRING") {
                $request->setQueryString($value);
            } elseif ($key == "REQUEST_TIME") {
                $request->setRequestTime($value);
            }
        }
    }
    
    /**
     * Prepare response
     * 
     * This method is invoked by the front controller before invoking the 
     * requested action in the action controller. It gives the client an 
     * opportunity to do something before the controller is executed.
     * 
     * @param PHPFrame_Response $response   The response object to prepare.
     * @param string            $views_path Absolute path to vies dir.
     * 
     * @return void
     * @since  1.0
     */
    public function prepareResponse(PHPFrame_Response $response, $views_path)
    {
        global $HTTP_RAW_POST_DATA;
        
        // Before we proceed to prepare the response we authenticate
        try {
            $this->_authenticate($HTTP_RAW_POST_DATA);
            $this->_checkAPIPermisssions();
        } catch (PHPFrame_XMLRPCException $e) {
            echo $e->getXMLRPCFault();
            exit;
        }
        
        // Set document as response content
        $response->document(new PHPFrame_RPCDocument());
        
        // Set response renderer
        $response->renderer(new PHPFrame_RPCRenderer());     
    }
    
    /**
     * Handle controller redirection
     * 
     * @param string $url The URL we want to redirect to.
     * 
     * @return void
     * @since  1.0
     * @todo   This method needs to be implemented.
     */
    public function redirect($url)
    {
        // ...
    }
    
    /**
     * This method is used to parse an XML remote procedure call
     * 
     * @param string           $xml     A string containing the XML call.
     * @param PHPFrame_Request $request Reference to the request object.
     * 
     * @return array A nice asociative array with all the data.
     * @since  1.0
     */
    private function _parseXMLRPC($xml, PHPFrame_Request $request) 
    {
        $array = array();
        
        $domDocument = new DOMDocument;
        $domDocument->preserveWhiteSpace = false;
        $domDocument->loadXML($xml);
        $domXPath = new DOMXPath($domDocument);
        
        $query = "//methodCall/methodName";
        $query_result = $domXPath->query($query);
        $methodName = $query_result->item(0)->nodeValue;
        //look for 'controller.action' format 
        preg_match('/^([a-zA-Z]+)(\.([a-zA-Z_]+))?$/', $methodName, $matches);
        
        //matches?
        if (count($matches) > 0) {
            //first match is controller
            $request->setControllerName($matches[1]);
            //third match is action (if it exists) 
            if (count($matches) > 2) {
                $request->setAction($matches[3]);   
            }
        }
        
        $query = '//methodCall/params/param/value';
        $query_result = $domXPath->query($query);
        //look at the first struct element members to identify parameter values
        if ($query_result instanceof DOMNodeList 
            && $query_result->length!=0 
            && $query_result->item(0)->hasChildNodes()
        ) {
            $parameters = array();
            foreach ($query_result as $parameter) {
                $parameters[] = $this->_parseXMLRPCRecurse($domXPath, $parameter);
            }
            try {
                //check if controller action request is valid:
                $paramMap = $this->_getComponentActionParameterMapping(
                    $matches[1], 
                    $matches[3], 
                    $parameters
                );
            } catch (PHPFrame_XMLRPCException $e) {
                echo $e->getXMLRPCFault();
                exit;
            }
            
            foreach ($paramMap as $key=>$value) {
                $request->setParam($key, $value);
            }
        } else {
            try {
                //check if controller action request is valid:
                $paramMap = $this->_getComponentActionParameterMapping(
                    $matches[1], 
                    $matches[3], 
                    array()
                );
            } catch (PHPFrame_XMLRPCException $e) {
                echo $e->getXMLRPCFault();
                exit;
            }
        }

        return $array;
    }
       
    /**
     * Parses an xml-rpc value node and returns the correct data type.
     * 
     * @param object  $domXPath The DOMXPath object used for parsing the XML. 
     *                          This object is created in _parseXMLResponse().
     * @param DOMNode $node     The value DOMNode to parse.
     * 
     * @return Various if the given node is a scalar value, the scalar value is 
     *         returned, if the node is a struct, an associative array with key 
     *         value pairs is returned, if the node is an array
     * @since  1.0
     */
    private function _parseXMLRPCRecurse($domXPath, $node)
    {
        if (!(($node instanceof DOMNode) && $node->nodeName=='value')) {
            $msg  = "Invalid parameter type, nodes must be of type DOMNode ";
            $msg .= "and must be a value node!";
            throw new InvalidArgumentException($msg);
        }
         
        //check if current value is a struct, array or scalar type
        if ($node->firstChild->nodeName=='struct') {
            $newStruct = array();
            $query = 'struct/member';
            $members = $domXPath->query($query, $node);
            foreach ($members as $member) {
                $query = 'name';
                $key   = $domXPath->query($query, $member)->item(0)->nodeValue;
                $query = 'value';
                $value = $this->_parseXMLRPCRecurse(
                    $domXPath, 
                    $domXPath->query($query, $member)->item(0)
                );
                
                $newStruct[$key] = $value;
            }
            
            return $newStruct;
            
        } else if ($node->firstChild->nodeName=='array') {
            $newArray = array();
            $query    = 'array/data/value';
            $values   = $domXPath->query($query, $node);
            
            foreach ($values as $value) {
                $newArray[] = $this->_parseXMLRPCRecurse($domXPath, $value);
            }
            
            return $newArray;
            
        } else {//value node must a scalar type
            $leafValue = $node->firstChild;
            return $this->_nodeScalarValue($leafValue);
        }
    }
    
    /**
     * Returns an associative array mapping the given parameters for a 
     * controller action, first checking if the call is valid.
     * This method first checks if the controller is valid, then 
     * continues to check if the action name is valid and finally, 
     * whether the parameters are valid. If all the checks pass, an 
     * associative array mapping the real controller action parameters names 
     * to the user specified parameters.
     * 
     * @param string $controller The name of the controller
     * @param string $action     The name of the action to check on the controller
     * @param array  $params     The indexed array of parameters required for 
     *                           the controller action.
     * 
     * @return mixed Either an array containing paramter mapping or void 
     *               with thrown PHPFrame_XMLRPCException if controller, action 
     *               or parameters are invalid
     * @since  1.0
     */
    private function _getComponentActionParameterMapping(
        $controller, 
        $action, 
        $params
    ) {
        $reflectionClass = $this->_getControllerClass($controller);
        if (!$reflectionClass) {
            throw new PHPFrame_XMLRPCException(
                'No such controller exists: '.$controller, 
                PHPFrame_XMLRPCException::INVALID_COMPONENT
            );
            return;
        }
        
        if (!$reflectionClass->hasMethod($action)) {
            $msg  = 'No such action: '.$action.' exists in controller: ';
            $msg .= $controller;
            throw new PHPFrame_XMLRPCException(
                $msg, 
                PHPFrame_XMLRPCException::INVALID_ACTION
            );
            return;
        }
        
        $actionMethod = $reflectionClass->getMethod($action);
        if (!$actionMethod->isPublic()) {
            $msg  = 'Action: '.$action.' is inaccessible in controller: ';
            $msg .= $controller;
            throw new PHPFrame_XMLRPCException(
                $msg, 
                PHPFrame_XMLRPCException::INVALID_ACTION
            );
            return;
        }
        
        $reflectionParameters = $actionMethod->getParameters();
        $numParams = count($reflectionParameters);
        $minReqParams = $actionMethod->getNumberOfRequiredParameters();
        if (count($params) > $numParams) {
            $msg  = 'Too many parameters for action: '.$action;
            $msg .= ' in controller: '.$controller;
            throw new PHPFrame_XMLRPCException(
                $msg, 
                PHPFrame_XMLRPCException::INVALID_NUMBER_PARAMETERS
            );
            
            return;
            
        } elseif (count($params) < $minReqParams) {
            $msg  = 'Too few parameters for action: '.$action;
            $msg .= ' in controller: '.$controller;
            throw new PHPFrame_XMLRPCException(
                $msg,
                PHPFrame_XMLRPCException::INVALID_NUMBER_PARAMETERS
            );
        }
        
        $paramMap = array();
        $paramIndex = 0;
        foreach ($reflectionParameters as $reflectionParam) {
            if ($paramIndex<count($params)) {
                $class = $reflectionParam->getClass();
                $paramPosition = $reflectionParam->getPosition();
                if ($reflectionParam->isArray() 
                    && !is_array($params[$paramPosition])
                ) {
                    $msg  = 'Parameter type mis-match for parameter ';
                    $msg .= $paramPosition.', expected an array, got ';
                    $msg .= 'primitive type';
                    throw new PHPFrame_XMLRPCException(
                        $msg, 
                        PHPFrame_XMLRPCException::INVALID_PARAMETER_TYPE
                    );
                    return;
                } elseif (!empty($class) && !is_array($params[$paramPosition])) {
                    $msg  = 'Parameter type mis-match for parameter ';
                    $msg .= $paramPosition.', expected a struct, got ';
                    $msg .= 'primitive type';
                    throw new PHPFrame_XMLRPCException(
                        $msg, 
                        PHPFrame_XMLRPCException::INVALID_PARAMETER_TYPE
                    );
                    return;
                } elseif (
                    !$reflectionParam->isArray() 
                    && empty($class) 
                    && is_array($params[$paramPosition])
                ) {
                    $msg  = 'Parameter type mis-match for parameter ';
                    $msg .= $paramPosition.', expected a primitive, got a ';
                    $msg .= "struct/array";
                    throw new PHPFrame_XMLRPCException(
                        $msg, 
                        PHPFrame_XMLRPCException::INVALID_PARAMETER_TYPE
                    );
                    return;
                } else {
                    $param_name = $reflectionParam->getName();
                    $paramMap[$param_name] = $params[$paramPosition];
                }
                $paramIndex++;
            } else {
                break;
            }   
        }
        
        return $paramMap;
    }
    
    /**
     * Gets the specified controller controller class if it exists. 
     * This returns the ReflectionClass object if there is an instantiable 
     * controller class for the specified controller.
     * 
     * @param string $controller The controller name.
     * 
     * @return mixed ReflectionClass if a controller with this name exists, 
     *               FALSE otherwise.
     * @since  1.0
     */
    private function _getControllerClass($controller)
    {
        $class_name = ucfirst($controller)."Controller";
        
        // make a reflection object
        try{
            $reflectionObj = new ReflectionClass($class_name);
        } catch (Exception $e){
            return false;
        }
        
        // Check if class is instantiable
        if ($reflectionObj->isInstantiable()) {
            // Try to get the constructor
            $constructor = $reflectionObj->getConstructor();
            // Check to see if we have a valid constructor method
            if ($constructor instanceof ReflectionMethod) {
                // If constructor is public we create a new instance
                if ($constructor->isPublic()) {
                    return $reflectionObj;
                }
            }
        }
        
        //check if the class has a static getInstance method
        if ($reflectionObj->hasMethod('getInstance')) {
            $get_instance = $reflectionObj->getMethod('getInstance');
            if ($get_instance->isPublic() && $get_instance->isStatic()) {
                return $reflectionObj;
            }
        }
    }    
    
    
    /**
     * This method is used to return the scalar value of a DOMNode. 
     * The node must be one of the scalar values as specified by the 
     * xml rpc (i4, int, boolean, string, double, dateTime.iso8601, base64).
     * 
     * @param DOMNode $node DOMNode object containing value to return.
     * 
     * @return mixed int for i4, int or dateTime.iso8601 (unix timestamp) nodes, 
     *               boolean for boolean, string for string or base64 and float 
     *               for double.
     * @since  1.0
     */
    private function _nodeScalarValue($node)
    {
        if (!($node instanceof DOMNode)) {
            $msg = "Invalid parameter, node must be of type DOMNode!";
            throw new InvalidArgumentException($msg);
        }
        
        $nodeName  = $node->nodeName;
        $time_reg  = '/(^[0-9]{4})([0-9]{2})([0-9]{2})T';
        $time_reg .='([0-9]{2}):([0-9]{2}):([0-9]{2}$)/';
        switch ($nodeName){
        case 'i4':
        case 'int':
            $value = (int) $node->nodeValue;
            break;
            
        case 'boolean':
            $value = (boolean) $node->nodeValue;
            break;
                
        case 'string':
        case 'base64':
            $value = (string) $node->nodeValue;
            break;
                
        case 'double':
            $value = (float) $node->nodeValue;
            break;
                
        case 'dateTime.iso8601':
            $matches = array();
            $isValidTime = preg_match($time_reg, $node->nodeValue, $matches);
            
            if ($isValidTime!=1) {
                $msg  = "Invalid dateTime format found for value ";
                $msg .= $node->nodeValue."!";
                throw new DomainException($msg);
            } else {
                $value = mktime(
                    $matches[4], 
                    $matches[5], 
                    $matches[6], 
                    $matches[2], 
                    $matches[3], 
                    $matches[1]
                );
            }
            
            break;
            
        default:
            $value = "";
        }
    
        return $value;
    }
    
    /**
     * Checks whether the current request is from a valid XMLRPC API client. 
     * This method inspects the request header keys for X-API-USERNAME and 
     * X-API-SIGNATURE. If the API user is a valid user and the signature 
     * matches the special hashing function (using the private API key shared 
     * by both the client and server) of the xml payload, then the client is 
     * deemed to authenticated. Otherwise a <code>PHPFrame_XMLRPCException</code> 
     * exception with fault code <code>INVALID_API_KEY_OR_USER</code> is thrown.
     *  
     * @param string $xml_payload The complete XML-RPC call string used to test 
     *                            the api signature against.
     *                             
     * @return void or throws Exception if api authentication fails.
     * @since  1.0
     */
    private function _authenticate($xml_payload)
    {
        if (isset($_SERVER["HTTP_X_API_USERNAME"])) {
            $x_api_user = $_SERVER["HTTP_X_API_USERNAME"];
        }
        
        if (isset($_SERVER["HTTP_X_API_SIGNATURE"])) {
            $x_api_signature = $_SERVER["HTTP_X_API_SIGNATURE"];
        }
        
        // Get API user's key
        try {
            $sql     = "SELECT `key` FROM #__api_users ";
            $sql    .= " WHERE user = '".$x_api_user."'";
            $params  = array(":user"=>$x_api_user);
            $api_key = PHPFrame::DB()->fetchColumn($sql, $params);
            
            $test_signature = md5(md5($xml_payload.$api_key).$api_key);
            
            if ($x_api_signature === $test_signature) {
                // Login the user as group API
                $user = new PHPFrame_User();
                $user->setId(2);
                $user->setGroupId(4);
                $user->setUserName("api");
                $user->setFirstName('API');
                $user->setLastName('User');
                
                // Store user in session
                $session = PHPFrame::getSession();
                $session->setUser($user);
                
                // Automatically set session token in request so that forms will 
                // be allowed
                PHPFrame::Request()->setParam($session->getToken(), 1);
                
                return;
                
            } else {
                $msg  = "XMLRPC API authentication failed. ";
                $msg .= "API key not valid.";
                throw new RuntimeException($msg);
            }
            
        } catch (Exception $e) {
            $msg  = "XMLRPC API authentication failed";
            $code = PHPFrame_XMLRPCException::INVALID_API_KEY_OR_USER;
            throw new PHPFrame_XMLRPCException($msg, $code);
        }
    }
    
    /**
     * Checks whether the XMLRPC client is able to perform the requested action, 
     * throws a <code>PHPFrame_XMLRPCException</code> with fault code 
     * <code>INVALID_PERMISSIONS</code> if not authorized.
     * 
     * @return void or throws PHPFrame_XMLRPCException if XMLRPC client is not 
     *         authorized to perform the requested action
     * @since  1.0
     */
    private function _checkAPIPermisssions()
    {
        // Check permissions before we execute
        $controller  = PHPFrame::Request()->getControllerName();
        $action      = PHPFrame::Request()->getAction();
        $groupid     = PHPFrame::getSession()->getGroupId();
        $permissions = PHPFrame::AppRegistry()->getPermissions();
        
        if ($permissions->authorise($controller, $action, $groupid) !== true) {
            $msg  = "Insufficient XMLRPC API permissions to perform action. ";
            $msg .= "XMLRPC client is not allowed to ";
            $msg .= "perform the action: $action on controller: $controller.";
            throw new PHPFrame_XMLRPCException(
                $msg, 
                PHPFrame_XMLRPCException::INVALID_PERMISSIONS
            );
        }
    }
}
