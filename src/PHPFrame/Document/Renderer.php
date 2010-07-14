<?php
/**
 * PHPFrame/Document/Renderer.php
 *
 * PHP version 5
 *
 * @category  PHPFrame
 * @package   Document
 * @author    Lupo Montero <lupo@e-noise.com>
 * @copyright 2010 The PHPFrame Group
 * @license   http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link      http://github.com/PHPFrame/PHPFrame
 */

/**
 * Renderer interface
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @since    1.0
 */
abstract class PHPFrame_Renderer
{
    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     */
    abstract public function render($value);

    public function exceptionToArray(Exception $e)
    {
        $array = array();
        $array["request"] = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $array["message"]  = $e->getMessage();
        $array["code"] = $e->getCode();
        $array["timestamp"] = date("D M d H:i:s O Y");

        if (array_key_exists("HTTPS", $_SERVER)) {
            $array["request"] = "https://".$array["request"];
        } else {
            $array["request"] = "http://".$array["request"];
        }

        return array("error"=>$array);
    }
}
