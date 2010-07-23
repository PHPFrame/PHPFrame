<?php
/**
 * PHPFrame/Document/XMLRenderer.php
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
 * XML renderer class
 *
 * @category PHPFrame
 * @package  Document
 * @author   Lupo Montero <lupo@e-noise.com>
 * @license  http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://github.com/PHPFrame/PHPFrame
 * @see      PHPFrame_IRenderer
 * @since    1.0
 */
class PHPFrame_XMLRenderer extends PHPFrame_Renderer
{
    private $_root_node_name = "root";

    public function rootNodeName($str=null)
    {
        if (!is_null($str)) {
            $this->_root_node_name = $str;
        }

        return $this->_root_node_name;
    }

    /**
     * Render a given value.
     *
     * @param mixed $value The value we want to render.
     *
     * @return void
     * @since  1.0
     */
    public function render($value)
    {
        if ($value instanceof Exception) {
            $value = $this->exceptionToArray($value);
        } elseif ($value instanceof PHPFrame_PersistentObject) {
            $value = $this->persistentObjectToArray($value);
        } elseif ($value instanceof PHPFrame_PersistentObjectCollection) {
            $value = $this->persistentObjectCollectionToArray($value);
        }

        if (!is_array($value) && !is_object($value)) {
            return (string) $value;
        }

        return PHPFrame_XMLSerialiser::serialise($value, $this->rootNodeName());
    }
}
