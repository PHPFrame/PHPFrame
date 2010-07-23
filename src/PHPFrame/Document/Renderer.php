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

    /**
     * Convert Exception object to associative array.
     *
     * @param Exception $e Instance of exception.
     *
     * @return array
     * @since  1.2
     */
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

    /**
     * Convert PersistentObject to associative array.
     *
     * @param PHPFrame_PersistentObject $obj Instance of persistent object.
     *
     * @return array
     * @since  1.2
     */
    public function persistentObjectToArray(PHPFrame_PersistentObject $obj)
    {
        $props = array();

        foreach (get_object_vars($obj) as $prop_name=>$prop_value) {
            if ($prop_value instanceof PHPFrame_PersistentObjectCollection) {
                $props[$prop_name] = $this->persistentObjectCollectionToArray($prop_value);
            } elseif ($prop_value instanceof PHPFrame_PersistentObject) {
                $props[$prop_name] = $this->persistentObjectToArray($prop_value);
            } elseif ($prop_value instanceof SplObjectStorage) {
                $props[$prop_name] = array();
                foreach ($prop_value as $child) {
                    $props[$prop_name][] = $this->persistentObjectToArray($child);
                }
            } else {
                $props[$prop_name] = $prop_value;
            }
        }

        $array = iterator_to_array($obj);

        if ($obj instanceof PHPFrame_User) {
            unset($array["password"]);
        }

        return array_merge($array, $props);
    }

    /**
     * Convert PersistentObjectCollection to associative array.
     *
     * @param PHPFrame_PersistentObjectCollection $collection Collection object.
     *
     * @return array
     * @since  1.2
     */
    public function persistentObjectCollectionToArray(
        PHPFrame_PersistentObjectCollection $collection
    ) {
        $array = array();

        foreach ($collection as $obj) {
            $array[] = $this->persistentObjectToArray($obj);
        }

        return $array;
    }
}
