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
    private $_format;

    /**
     * Constructor.
     *
     * @param PHPFrame_Application $app Reference to application object.
     *
     * @return void
     * @since  1.0
     */
    public function __construct(PHPFrame_Application $app)
    {
        parent::__construct($app, "usage");

        $request = $this->request();
        $this->request()->ajax(true);

        $action = $request->action();
        if (!$action) {
            $method = $request->method();
            switch ($method) {
            case "GET" :
                $request->action("get"); break;
            case "POST" :
                $request->action("post"); break;
            case "DELETE" :
                $request->action("delete"); break;
            default :
                $request->action("usage");
            }
        }

        $this->_format = $request->param("format");
        if (!$this->_format) {
            $this->_format = "json";
        }

        switch ($this->_format) {
        case "xml" :
            $this->response()->document(new PHPFrame_XMLDocument());
            $this->response()->document()->useBeautifier(false);
            $this->response()->renderer(new PHPFrame_XMLRenderer());
            $this->response()->renderer()->rootNodeName("api-response");
            break;

        case "php" :
            $this->response()->document(new PHPFrame_PlainDocument());
            $this->response()->renderer(new PHPFrame_PHPSerialisedDataRenderer());
            break;

        default :
            $this->response()->document(new PHPFrame_PlainDocument());
            $this->response()->renderer(new PHPFrame_JSONRenderer(false));
            $this->response()->header("Content-Type", "application/json");

            if ($this->_format !== "json") {
                throw new Exception("Unknown value for parameter 'format'!", 400);
            }

            break;
        }
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
                foreach ($method->getParameters() as $arg) {
                    $arg = get_object_vars($arg);
                    $array = array();
                    $array[$arg["name"]] = array();

                    if (array_key_exists("type", $arg)) {
                        $array[$arg["name"]]["type"] = $arg["type"];
                    }

                    if (array_key_exists("description", $arg)) {
                        $array[$arg["name"]]["description"] = $arg["description"];
                    }

                    $args[] = $array;
                }

                $ret_obj->methods[] = array(
                    strtolower(str_replace("Api", "", $api_name))."/".$method->getName() => array(
                        "description" => $method->getDescription(),
                        "signature" => $method->getSignature(),
                        "args" => $args,
                    )
                );
            }
        }

        $this->response()->body($ret_obj);
    }

    // abstract public function get($id=null, $limit=0, $page=1);
    // abstract public function post();
    // abstract public function delete($id);
}
