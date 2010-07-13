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
        parent::__construct($app, "index");
    }

    /**
     * If no method is specified as a controller name we try to map using HTTP
     * methods.
     *
     * @return void
     * @since  1.0
     */
    public function index()
    {
        $params = $this->request()->params();
        $method = $this->request()->method();

        switch ($method) {
        case "GET" :
            $id    = null;
            $limit = 0;
            $page  = 1;

            if (array_key_exists("id", $params)) {
                $id = $params["id"];
            } else {
                if (array_key_exists("limit", $params)) {
                    $limit = $params["limit"];
                }
                if (array_key_exists("page", $params)) {
                    $page = $params["page"];
                }
            }

            return $this->get($id, $limit, $page);

        case "POST" :
            if (!array_key_exists("title", $params)) {
                $msg = "Parameter 'title' is required!";
                $this->raiseError($title);
                return;
            }

            $title  = $params["title"];
            $body   = null;
            $type   = "info";
            $sticky = false;
            $id     = null;

            if (array_key_exists("body", $params)) {
                $body = $params["body"];
            }

            if (array_key_exists("type", $params)) {
                $type = $params["type"];
            }

            if (array_key_exists("sticky", $params)) {
                $sticky = $params["sticky"];
            }

            if (array_key_exists("id", $params)) {
                $id = $params["id"];
            }

            return $this->post($title, $body, $type, $sticky, $id);

        case "DELETE" :
            if (!array_key_exists("id", $params)) {
                $msg = "Parameter 'id' is required!";
                $this->raiseError($msg);
                return;
            }

            return $this->delete($params["id"]);

        default:
            return $this->usage();
        }
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
        $ret_obj->api  = $config->get("app_name")." ".$api_name." ";
        $ret_obj->api .= "JSON API";
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
                    strtolower($api_name)."/".$method->getName() => array(
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
