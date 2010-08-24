<?php
/**
 * PHPFrame/MVC/RESTfulController.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   MVC
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * This class extends ActionController and defines an interface to be
 * implemented by RESTful controllers.
 *
 * @category PHPFrame
 * @package  MVC
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_View
 * @since    1.0
 * @abstract
 */
abstract class PHPFrame_RESTfulController extends PHPFrame_ActionController
{
    private $_format, $_return;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app    Reference to application object.
     * @param bool                 $return [Optional] Get/set whether API method
     *                                     should return to the calling PHP code
     *                                     instead of writing the output in the
     *                                     response object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app, $return=false)
    {
        parent::__construct($app, "usage");

        $request = $this->request();
        $action  = $request->action();
        if (!$action) {
            $method = $request->method();
            switch ($method) {
            case "GET" :
                $request->action("get");
                break;
            case "POST" :
                $request->action("post");
                break;
            case "DELETE" :
                $request->action("delete");
                break;
            default :
                $request->action("usage");
            }
        }

        $format = $request->param("format");
        if (!$format) {
            $format = "json";
        }

        $this->returnInternalPHP($return);
        $this->format($format);
    }

    /**
     * Throw exceptions instead of raising errors.
     *
     * @param string $msg The error message.
     *
     * @return void
     * @since  1.0
     */
    public function raiseError($msg)
    {
        throw new Exception($msg, $this->response()->statusCode());
    }

    /**
     * Get information about the RESTful API.
     *
     * @return void
     * @since  1.0
     */
    public function usage()
    {
        $config = $this->config();
        $api_name = str_replace("Controller", "", get_class($this));

        $ret_obj = new StdClass();
        $ret_obj->api  = $config->get("app_name")." ".str_replace("Api", "", $api_name)." ";
        $ret_obj->api .= "RESTful API";
        $ret_obj->version = $config->get("version");
        $ret_obj->url = $config->get("base_url");

        date_default_timezone_set('UTC');
        $ret_obj->timestamp = date("D M d H:i:s O Y");

        $ret_obj->methods = array();

        $reflection_obj = new PHPFrame_ControllerDoc($this);
        foreach ($reflection_obj->getActions() as $method) {
            if ($method->getName() != "index") {
                $args = array();
                foreach ($method->getParameters() as $param) {
                    $arg = get_object_vars($param);
                    $array = array();
                    $array[$arg["name"]] = array();
                    $array[$arg["name"]]["required"] = !$param->isOptional();

                    if (array_key_exists("type", $arg)) {
                        $array[$arg["name"]]["type"] = $arg["type"];
                    }

                    if (array_key_exists("description", $arg)) {
                        $array[$arg["name"]]["description"] = $arg["description"];
                    }

                    $args[] = $array;
                }

                $printable_method_name  = str_replace("Api", "", $api_name);
                $printable_method_name  = strtolower($printable_method_name);
                $printable_method_name .= "/".$method->getName();
                $method_array = array(
                    "signature" => $method->getSignature(),
                    "description" => $method->getDescription()
                );

                if (count($args) > 0) {
                    $method_array["args"] = $args;
                }

                $method_array["return"] = array(
                    "type" => $method->getReturnType(),
                    "description" => $method->getReturnDescription()
                );

                $ret_obj->methods[] = array($printable_method_name => $method_array);
            }
        }

        $this->response()->body($ret_obj);
    }

    /**
     * Get/set response format.
     *
     * @param string $str [Optional] Supported formats are 'xml', 'php' or
     *                    'json'.
     *
     * @return string
     * @since  1.2.5
     */
    public function format($str=null)
    {
        if (!is_null($str)) {
            if (!$this->returnInternalPHP()) {
                $this->_setResponse($str);
            }

            $this->_format = $str;
        }

        return $this->_format;
    }

    /**
     * Get/set whether API method should return to the calling PHP code instead
     * of writing the output in the response object.
     *
     * @param bool $bool [Optional]
     *
     * @return bool
     * @since  1.2.5
     */
    public function returnInternalPHP($bool=null)
    {
        if (!is_null($bool)) {
            $this->_return = (bool) $bool;
        }

        return $this->_return;
    }

    /**
     * Handle return value based on whether controller is set to return to
     * calling PHP code or not.
     *
     * @param mixed $mixed The return value.
     *
     * @return mixed
     * @since  1.2.5
     */
    public function handleReturnValue($mixed)
    {
        if ($this->returnInternalPHP()) {
            return $mixed;
        } else {
            $this->response()->body($mixed);
        }
    }

    /**
     * Set response object according to format.
     *
     * @param string $str [Optional] Supported formats are 'xml', 'php' or
     *                    'json'.
     *
     * @return mixed
     * @since  1.2.5
     */
    private function _setResponse($format)
    {
        $response = $this->response();

        switch ($format) {
        case "xml" :
            $response->document(new PHPFrame_XMLDocument());
            $response->document()->useBeautifier(false);
            $response->renderer(new PHPFrame_XMLRenderer());
            $response->renderer()->rootNodeName("api-response");
            break;

        case "xmlrpc" :
            $response->document(new PHPFrame_XMLDocument());
            $response->document()->useBeautifier(false);
            $response->renderer(new PHPFrame_RPCRenderer($response->document()));
            break;

        case "php" :
            $response->document(new PHPFrame_PlainDocument());
            $response->renderer(new PHPFrame_PHPSerialisedDataRenderer(true));
            $response->header("Content-Type", "application/php");
            break;

        default :
            $response->document(new PHPFrame_PlainDocument());
            $response->renderer(new PHPFrame_JSONRenderer(true));
            $response->header("Content-Type", "application/json");

            $jsonp_callback = $this->request()->param("jsonp_callback");
            if ($jsonp_callback) {
                $response->renderer()->jsonpCallback($jsonp_callback);
            }

            if ($format !== "json") {
                $msg = "Unknown value for parameter 'format'!";
                throw new Exception($msg, 400);
            }

            break;
        }
    }
}
