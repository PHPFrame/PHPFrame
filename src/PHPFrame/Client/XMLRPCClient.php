<?php
/**
 * PHPFrame/Client/XMLRPCClient.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Client
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * XMLRPC Client implementation
 *
 * @category PHPFrame
 * @package  Client
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
class PHPFrame_XMLRPCClient extends PHPFrame_Client
{
    /**
     * Initialise DOM object with XML passed in request body.
     *
     * @param string $xml_payload String containing the request XML.
     *
     * @return DOMDocument
     * @throws InvalidArgumentException, RuntimeException
     * @since  1.0
     */
    private function _createDom($xml_payload)
    {
        $request_dom  = new DOMDocument();
        $request_dom->preserveWhiteSpace = false;
        if ($request_dom->loadXML($xml_payload)) {
            $xpath = new DOMXPath($request_dom);
            //check for valid RPC structure
            $query       = "//methodCall/methodName";
            $method_node = $xpath->query($query)->item(0);
            if ($method_node->nodeValue == null) {
                $msg = "Invalid XML payload.";
                throw new InvalidArgumentException($msg);
            }
        } else {
            $msg  = "Invalid XML-RPC request. The request body doesn't ";
            $msg .= "contain valid XML.";
            throw new RuntimeException($msg);
        }

        return $request_dom;
    }

    /**
     * Check if this is the correct helper for the client being used
     *
     * @return PHPFrame_Client|boolean Object instance of this class if correct
     *                                 helper for client or FALSE otherwise.
     * @since  1.0
     */
    public static function detect()
    {
        if (isset($_SERVER["CONTENT_TYPE"])
            && $_SERVER["CONTENT_TYPE"] == "text/xml"
        ) {
            return new self();
        }

        return false;
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
        $this->_parseXMLRPCRequest($request);

        $request->requestTime(time());
        $request->quiet(false);

        foreach ($_SERVER as $key=>$value) {
            if (substr($key, 0, 5) == "HTTP_") {
                $key = str_replace('_', ' ', substr($key, 5));
                $key = str_replace(' ', '-', ucwords(strtolower($key)));
                $request->header($key, $value);
            } elseif ($key == "REQUEST_METHOD") {
                $request->method($value);
            } elseif ($key == "REMOTE_ADDR") {
                $request->remoteAddr($value);
            } elseif ($key == "REQUEST_URI") {
                $request->requestURI($value);
            } elseif ($key == "SCRIPT_NAME") {
                $request->scriptName($value);
            } elseif ($key == "QUERY_STRING") {
                $request->queryString($value);
            } elseif ($key == "REQUEST_TIME") {
                $request->requestTime($value);
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
        // Set document as response content
        $response->document(new PHPFrame_XMLDocument());
        $response->document()->useBeautifier(false);

        // Set response renderer
        $response->renderer(new PHPFrame_RPCRenderer($response->document()));

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
     * @param PHPFrame_Request $request Reference to the request object.
     *
     * @return array A nice associative array with all the data.
     * @since  1.0
     */
    private function _parseXMLRPCRequest(PHPFrame_Request $request)
    {
        $array = array();
        $xpath = new DOMXPath($this->_createDom($request->body()));
        $query_result = $xpath->query("//methodCall/methodName");
        $method_name = $query_result->item(0)->nodeValue;
        //look for 'controller.action' format
        preg_match('/^([a-zA-Z]+)(\.([a-zA-Z_]+))?$/', $method_name, $matches);

        //matches?
        if (count($matches) > 0) {
            //first match is controller
            $request->controllerName($matches[1]);
            //third match is action (if it exists)
            if (count($matches) > 2) {
                $request->action($matches[3]);
            }
        }

        $controller_class = ucfirst($matches[1])."Controller";

        try {
            $controller_reflector = new ReflectionClass($controller_class);
        } catch (ReflectionException $e) {
            return;
        }

        if (!$controller_reflector->hasMethod($matches[3])) {
            return;
        }

        $action_reflector = $controller_reflector->getMethod($matches[3]);
        if (!$action_reflector instanceof ReflectionMethod) {
            return;
        }

        $value_nodes  = $xpath->query("//methodCall/params/param/value");
        $param_values = array();
        foreach ($value_nodes as $value_node) {
            $param_values[] = $this->_parseXMLRPCValue($value_node, $xpath);
        }

        $param_count = 0;
        foreach ($action_reflector->getParameters() as $param_reflector) {
            if (array_key_exists($param_count, $param_values)) {
                $request->param(
                    $param_reflector->getName(),
                    $param_values[$param_count]
                );
                $param_count++;
            }
        }
    }

    /**
     * Parses an xml-rpc value node and returns the correct data type.
     *
     * @param DOMNode  $node  The value DOMNode to parse.
     * @param DOMXPath $xpath The DOMXPath object used for parsing the XML.
     *
     * @return Various if the given node is a scalar value, the scalar value is
     *         returned, if the node is a struct, an associative array with key
     *         value pairs is returned, if the node is an array
     * @since  1.0
     */
    private function _parseXMLRPCValue(DOMNode $node, DOMXPath $xpath)
    {
        if ($node->nodeName != "value") {
            $msg  = "Invalid parameter type, nodes must be of type DOMNode ";
            $msg .= "and must be a value node!";
            throw new InvalidArgumentException($msg);
        }

        $type_node = $node->firstChild;

        switch ($type_node->nodeName) {
        case "boolean":
            return (bool) $type_node->nodeValue;

        case "i4" :
        case "int" :
            return (int) $type_node->nodeValue;

        case "double" :
            return (float) $type_node->nodeValue;

        case "string" :
        case "base64" :
            return (string) $type_node->nodeValue;

        case "dateTime.iso8601" :
            $pattern  = "/(^[0-9]{4})([0-9]{2})([0-9]{2})T";
            $pattern .= "([0-9]{2}):([0-9]{2}):([0-9]{2}$)/";
            $matches  = array();

            if (!preg_match($pattern, $type_node->nodeValue, $matches)) {
                $msg  = "Invalid dateTime format found for value ";
                $msg .= $type_node->nodeValue."!";
                throw new DomainException($msg);
            } else {
                return mktime(
                    $matches[4],
                    $matches[5],
                    $matches[6],
                    $matches[2],
                    $matches[3],
                    $matches[1]
                );
            }

        case "struct" :
            $new_struct = array();
            $members    = $xpath->query("struct/member", $node);
            foreach ($members as $member) {
                $query = "name";
                $key   = $xpath->query($query, $member)->item(0)->nodeValue;
                $query = "value";
                $value = $this->_parseXMLRPCValue(
                    $xpath->query($query, $member)->item(0),
                    $xpath
                );

                $new_struct[$key] = $value;
            }

            return $new_struct;

        case "array" :
            $new_array = array();
            $values    = $xpath->query("array/data/value", $node);

            foreach ($values as $value) {
                $new_array[] = $this->_parseXMLRPCValue($value, $xpath);
            }

            return $new_array;

        default :
            return "";
        }
    }
}
