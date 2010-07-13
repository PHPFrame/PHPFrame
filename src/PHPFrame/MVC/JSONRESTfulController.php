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
 * This class extends {@link PHPFrame_RESTfulController} and defines an interface
 *  to be implemented by JSON implementations of RESTful controllers.
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
abstract class PHPFrame_JSONRESTfulController extends PHPFrame_RESTfulController
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
        parent::__construct($app);

        $this->request()->ajax(true);
        $this->response()->document(new PHPFrame_PlainDocument());
        $this->response()->renderer(new PHPFrame_JSONRenderer(false));
        $this->response()->header("Content-Type", "application/json");
    }

    /**
     * Override PHPFrame_ActionController::raiseError() to return nice JSON
     * error messages in the same style as the Twitter JSON API.
     *
     * @param string $msg The error message.
     *
     * @return void
     * @since  1.0
     */
    protected function raiseError($msg)
    {
        $ret_obj = new StdClass();
        $ret_obj->request = $_SERVER["SERVER_NAME"].$this->request()->requestURI();
        $ret_obj->error   = preg_replace("/OpenSRS/", "E-NOISE", $msg);

        if (array_key_exists("HTTPS", $_SERVER)) {
            $ret_obj->request = "https://".$ret_obj->request;
        } else {
            $ret_obj->request = "http://".$ret_obj->request;
        }

        $this->response()->body($ret_obj);
    }
}
